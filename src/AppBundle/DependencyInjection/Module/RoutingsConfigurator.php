<?php

namespace AppGear\AppBundle\DependencyInjection\Module;

use AppGear\CoreBundle\DependencyInjection\Module\ConfiguratorInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

/**
 * AppGear configuration for routing
 */
class RoutingsConfigurator implements ConfiguratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildNode()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('routings');

        $rootNode
            ->prototype('array')
            ->performNoDeepMerging()
            ->children()
            ->scalarNode('pattern')
            ->isRequired()
            ->end()
            ->scalarNode('controller')
            ->isRequired()
            ->end()
            ->arrayNode('defaults')
            ->prototype('variable')
            ->end()
            ->end()
            ->arrayNode('requirements')
            ->prototype('scalar')
            ->end()
            ->end()
            ->arrayNode('methods')
            ->prototype('scalar')
            ->end()
            ->end()
            ->end()
            ->end();

        return $rootNode;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container, $modulesConfigs)
    {
        $routesLoaderDef = new Definition('AppGear\AppBundle\Routing\AppGearLoader');
        $routesLoaderDef->addTag('routing.loader');

        foreach ($modulesConfigs as $routeName => $route) {
            $routesLoaderDef->addMethodCall(
                'addRoute', [
                    $routeName,
                    $route['controller'],
                    $route['pattern'],
                    $route['defaults'],
                    $route['requirements']
                ]
            );
        }

        $container->setDefinition('appgear.routing.loader', $routesLoaderDef);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'routings';
    }
}
