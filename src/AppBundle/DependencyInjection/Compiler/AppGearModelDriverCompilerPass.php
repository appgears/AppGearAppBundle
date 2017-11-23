<?php

namespace AppGear\AppBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class AppGearModelDriverCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        foreach ($container->getParameter('appgear.storage.drivers') as $name => $options) {
            if ($options['type'] !== 'doctrine-orm') {
                continue;
            }

            $def = new DefinitionDecorator('appgear.storage.driver.doctrine_orm.metadata_driver');
            $def->addArgument($options['prefixes']);

            $container->setDefinition("doctrine.orm.{$options['entity_manager']}_metadata_driver", $def);
        }
    }
}