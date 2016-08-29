<?php

namespace AppGear\AppBundle\DependencyInjection;

use Cosmologist\Gears\ArrayType;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AppGearAppExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('entities.yml');
        $loader->load('form.yml');
        $loader->load('services.yml');

        $container->setParameter('appgear.application.route404.enabled', $config['route404']['enabled']);
        $container->setParameter('appgear.application.route404.route', $config['route404']['route']);

        $this->loadDrivers($container, $config['storage']);
    }

    /**
     * Load drivers to the DriverManager
     *
     * @param ContainerBuilder $container Container builder
     * @param array            $config    Storage drivers config
     */
    private function loadDrivers(ContainerBuilder $container, $config)
    {
        $managerDef = $container->getDefinition('appgear.storage.driver.manager');

        $drivers = [];
        foreach ($container->findTaggedServiceIds('appgear.storage.driver') as $driverId => $driverAttributesGroups) {
            foreach ($driverAttributesGroups as $driverAttributes) {
                if (array_key_exists('alias', $driverAttributes)) {
                    $drivers[$driverAttributes['alias']] = $driverId;
                    break;
                }
            }
        }

        foreach ($config as $driver => $options) {
            if (!array_key_exists($driver, $drivers)) {
                throw new \RuntimeException(sprintf('Driver "%s" not found', $driver));
            }
            foreach ($options['prefixes'] as $prefix) {
                $managerDef->addMethodCall('addDriverPrefix', [$driver, $prefix]);
                $managerDef->addMethodCall('addDriver', [$driver, new Reference($drivers[$driver])]);
            }
        }
    }
}
