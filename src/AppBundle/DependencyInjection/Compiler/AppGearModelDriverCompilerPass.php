<?php

namespace AppGear\AppBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AppGearModelDriverCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $driverChainDef = $container->findDefinition('doctrine.orm.default_metadata_driver');

        $driverChainDef->addMethodCall('addDriver', array(
                new Reference('appgear.storage.driver.doctrine_orm.metadata.appgear_model_driver'),
                'Commerce\\PlatformBundle\\Entity'
            )
        );
    }
}