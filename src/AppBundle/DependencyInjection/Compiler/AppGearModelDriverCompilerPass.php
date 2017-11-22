<?php

namespace AppGear\AppBundle\DependencyInjection\Compiler;

use AppGear\AppBundle\DependencyInjection\Configuration;
use AppGear\AppBundle\Storage\Driver\DoctrineOrm\Metadata\AppGearModelDriver;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class AppGearModelDriverCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $processor     = new Processor;
        $config        = $processor->processConfiguration($configuration, $container->getExtensionConfig('app'));

        foreach ($config['storage']['drivers'] as $name => $options) {
            if ($options['type'] !== 'doctrine-orm') {
                continue;
            }

            $def = new Definition(AppGearModelDriver::class);
            $def->addMethodCall('setModelManager', [new Reference('app_gear.core.model.manager')]);
            $def->addMethodCall('setTaggedManager', [new Reference('app_gear.core.tagged_manager')]);
            $def->addMethodCall('setPrefixes', [$options['prefixes']]);
            $container->setDefinition("doctrine.orm.{$options['entity_manager']}_metadata_driver", $def);
        }
    }
}