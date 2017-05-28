<?php

namespace Symfgenus\GoogleGmailBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('symfgenus_google_gmail');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        $rootNode
            ->children()
                ->arrayNode('google_gmail')->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('application_name')->end()
                        ->scalarNode('credentials_path')->end()
                        ->scalarNode('client_secret_path')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
