<?php

namespace Symfgenus\GoogleGmailBundle\Service;

/**
 * Class GoogleGmail
 * @package Symfgenus\GoogleGmailBundle\Service
 *
 */
class GoogleGmail
{
    /**
     * @var string
     */
    protected $applicationName;

    /**
     * @var string
     */
    protected $credentialsPath;

    /**
     * @var string
     */
    protected $clientSecretPath;

    /**
     * @var string
     */
    protected $scopes;

    /**
     * @var string
     */
    protected $redirectUri;

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * @var string
     */
    protected $accessToken;

    /**
     * @var string
     */
    protected $refreshToken;

    /**
     * @var bool
     */
    protected $fromFile = true;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $approvalPrompt;

    /**
     * construct
     */
    public function __construct()
    {
        $this->type = 'offline';
        $this->approvalPrompt = 'force';
        $this->scopes = implode(' ', [\Google_Service_Gmail::MAIL_GOOGLE_COM]);
    }

    /**
     * @param $scope
     */
    public function addScope($scope)
    {
        $this->scopes .= ' ' . $scope;
    }

    /**
     * @param $scope
     */
    public function removeScope($scope)
    {
        $scopes = explode(' ', $this->scopes);
        if (($key = array_search($scope, $scopes)) !== false) {
            unset($scopes[$key]);
        }
        $this->scopes = implode(' ', $scopes);
    }

    /**
     * Add contact scope
     */
    public function addScopeContact()
    {
        $this->addScope(\Google_Service_Script::WWW_GOOGLE_COM_M8_FEEDS);
    }

    /**
     * Remove contact scope
     */
    public function removeScopeCalendar()
    {
        $this->removeScope(\Google_Service_Gmail::CALENDAR);
    }

    /**
     * Remove contact scope
     */
    public function removeScopeOffline()
    {
        $this->type = 'online';
        $this->approvalPrompt = 'auto';
    }

    /**
     * Remove contact scope
     */
    public function addScopeOffline()
    {
        $this->type = 'offline';
        $this->approvalPrompt = 'force';
    }

    /**
     * Add userinfo scope
     */
    public function addScopeUserInfos()
    {
        $this->addScope(\Google_Service_Oauth2::USERINFO_PROFILE);
        $this->addScope(\Google_Service_Oauth2::USERINFO_EMAIL);
    }

    /**
     * Remove userinfo scope
     */
    public function removeScopeUserInfos()
    {
        $this->removeScope(\Google_Service_Oauth2::USERINFO_PROFILE);
        $this->removeScope(\Google_Service_Oauth2::USERINFO_EMAIL);
    }

    /**
     * @param $applicationName
     */
    public function setApplicationName($applicationName)
    {
        $this->applicationName = $applicationName;
    }

    /**
     * @param $credentialsPath
     */
    public function setCredentialsPath($credentialsPath)
    {
        $this->credentialsPath = $credentialsPath;
    }

    /**
     * @param $clientSecretPath
     */
    public function setClientSecretPath($clientSecretPath)
    {
        $this->clientSecretPath = $clientSecretPath;
    }

    /**
     * @param $redirectUri
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;
    }

    /**
     * @param $parameters
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @param string $accessToken
     */
    public function setAccessToken($accessToken)
    {
        if ($accessToken != "") {
            $this->accessToken = $accessToken;
        }
    }

    /**
     * @param string $refreshToken
     */
    public function setRefreshToken($refreshToken)
    {
        if ($refreshToken != "") {
            $this->refreshToken = $refreshToken;
        }
    }

    /**
     * clear tokens
     */
    public function clearTokens()
    {
        $this->accessToken = "";
        $this->refreshToken = "";
    }

    /**
     * @param $inputStr
     *
     * @return string
     */
    public static function base64UrlEncode($inputStr)
    {
        return strtr(base64_encode($inputStr), '+/=', '-_,');
    }

    /**
     * @param $inputStr
     *
     * @return string
     */
    public static function base64UrlDecode($inputStr)
    {
        return base64_decode(strtr($inputStr, '-_,', '+/='));
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * @param null      $authCode
     * @param bool|true $fromFile
     *
     * @return \Google_Client|string
     */
    public function getClient($authCode = null, $fromFile = true)
    {
        $this->fromFile = $fromFile;

        $client = new \Google_Client();
        $client->setApplicationName($this->applicationName);
        $client->setScopes($this->scopes);
        $client->setAuthConfig($this->clientSecretPath);
        $client->setAccessType($this->type);
        $client->setApprovalPrompt($this->approvalPrompt);
        $client->setState($this->base64UrlEncode(json_encode($this->parameters)));

        // Load previously authorized credentials from a file.
        $credentialsPath = $this->credentialsPath;
        if ($fromFile) {
            if (file_exists($credentialsPath)) {
                $accessToken = json_decode(file_get_contents($credentialsPath), true);
            } else {
                // Request authorization from the user.
                if ($this->redirectUri) {
                    $client->setRedirectUri($this->redirectUri);
                }

                if ($authCode != null) {
                    $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

                    if (!file_exists(dirname($credentialsPath))) {
                        mkdir(dirname($credentialsPath), 0700, true);
                    }
                    file_put_contents($credentialsPath, json_encode($accessToken));
                } else {
                    return $client->createAuthUrl();
                }
            }
        } else {
            if ($this->accessToken != null) {
                $accessToken = json_decode($this->accessToken, true);
            } else {
                // Request authorization from the user.
                if ($this->redirectUri) {
                    $client->setRedirectUri($this->redirectUri);
                }

                if ($authCode != null) {
                    $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                    $this->accessToken = json_encode($accessToken);
                } else {
                    return $client->createAuthUrl();
                }
            }
        }
        $client->setAccessToken($accessToken);

        if ($client->getRefreshToken()) {
            $this->refreshToken = $client->getRefreshToken();
        }

        // Refresh the token if it's expired.
        if ($client->isAccessTokenExpired()) {
            if ($this->refreshToken) {
                $refreshToken = $this->refreshToken;
            } else {
                $refreshToken = $client->getRefreshToken();
            }

            if ($refreshToken) {
                $res = $client->fetchAccessTokenWithRefreshToken($refreshToken);
                if (!isset($res['access_token'])) {
                    return $client->createAuthUrl();
                }
                if ($fromFile) {
                    file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
                } else {
                    $this->accessToken = json_encode($client->getAccessToken());
                }
            } else {
                if ($fromFile) {
                    unlink($credentialsPath);
                } else {
                    $this->accessToken = null;
                }

                return $client->createAuthUrl();
            }
        }

        return $client;
    }

    /**
     * @return \Google_Service_Calendar|null
     */
    public function getGmailService()
    {
        $client = $this->getClient(null, $this->fromFile);
        if (!is_string($client)) {
            return new \Google_Service_Gmail($client);
        }

        return null;
    }

    /**
     * @return \Google_Service_Oauth2|null
     */
    public function getOauth2Service()
    {
        $client = $this->getClient(null, $this->fromFile);
        if (!is_string($client)) {
            return new \Google_Service_Oauth2($client);
        }

        return null;
    }

    /**
     * @param        $url
     * @param string $post
     *
     * @return mixed
     */
    public function curl($url, $post = "")
    {
        $curl = curl_init();
        $userAgent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)';
        curl_setopt($curl, CURLOPT_URL, $url);
        //The URL to fetch. This can also be set when initializing a session with curl_init().
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        //TRUE to return the transfer as a string of the return value of curl_exec() instead of outputting it out directly.
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        //The number of seconds to wait while trying to connect.
        if ($post != "") {
            curl_setopt($curl, CURLOPT_POST, 5);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        }
        curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);
        //The contents of the "User-Agent: " header to be used in a HTTP request.
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        //To follow any "Location: " header that the server sends as part of the HTTP header.
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);
        //To automatically set the Referer: field in requests where it follows a Location: redirect.
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        //The maximum number of seconds to allow cURL functions to execute.
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        //To stop cURL from verifying the peer's certificate.
        $contents = curl_exec($curl);
        curl_close($curl);

        return $contents;
    }

}
