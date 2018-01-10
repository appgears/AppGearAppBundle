<?php

namespace AppGear\AppBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * AppGearAppBundle configuration
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('appgear_app');

        $rootNode
            ->children()
                ->arrayNode('route404')
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultFalse()
                        ->end()
                        ->scalarNode('route')->end()
                    ->end()
                ->end()
                ->arrayNode('storage')
                    ->children()
                        ->scalarNode('default_driver')
                            ->defaultValue('appgear.storage.driver.yaml')
                        ->end()
                        ->arrayNode('drivers')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('type')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                    ->end()
                                    ->arrayNode('prefixes')
                                        ->prototype('scalar')->end()
                                    ->end()
                                    ->scalarNode('entity_manager')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('upload')
                    ->isRequired()
                    ->children()
                        ->scalarNode('directory')
                            ->isRequired()
                        ->end()
                        ->scalarNode('file_prefix')
                            ->isRequired()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
