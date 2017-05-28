<?php

namespace Symfgenus\GoogleGmailBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('SymfgenusGoogleGmailBundle:Default:index.html.twig');
    }
}
