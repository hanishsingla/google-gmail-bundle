<?php

namespace Symfgenus\GoogleGmailBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class SymfgenusGoogleGmailExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        if ($container->hasDefinition('symfgenus.google_gmail')) {
            $definition = $container->getDefinition('symfgenus.google_gmail');
            
            if (isset($config['google_gmail']['application_name'])) {
                $definition->addMethodCall('setApplicationName', [$config['google_gmail']['application_name']]);
            }

            if (isset($config['google_gmail']['credentials_path'])) {
                $definition->addMethodCall('setCredentialsPath', [$config['google_gmail']['credentials_path']]);
            }

            if (isset($config['google_gmail']['client_secret_path'])) {
                $definition->addMethodCall('setClientSecretPath', [$config['google_gmail']['client_secret_path']]);
            }
        }
    }

}
