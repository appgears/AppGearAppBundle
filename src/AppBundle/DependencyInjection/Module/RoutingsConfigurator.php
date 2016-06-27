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
            ->useAttributeAsKey('name')
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
                        ->useAttributeAsKey('name')
                        ->prototype('variable')
                        ->end()
                    ->end()
                    ->arrayNode('requirements')
                        ->useAttributeAsKey('name')
                        ->prototype('scalar')
                        ->end()
                    ->end()
                    ->arrayNode('methods')
                        ->prototype('scalar')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $rootNode;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container, $modulesConfigs)
    {
        $routesLoaderDef = new Definition('AppGear\AppBundle\Routing\AppGearLoader');
        $routesLoaderDef->addTag('routing.loader');

        foreach ($modulesConfigs as $moduleName=>$routes) {
            foreach ($routes as $routeName=>$route) {
                $routeAlias = $moduleName . '.' . $routeName;
                $routesLoaderDef->addMethodCall(
                    'addRoute', [
                        $routeAlias,
                        $route['controller'],
                        $route['pattern'],
                        $route['defaults'],
                        $route['requirements']
                    ]
                );
            }
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
