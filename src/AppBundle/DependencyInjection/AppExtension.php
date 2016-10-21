<?php

namespace AppGear\AppBundle\DependencyInjection;

use Cosmologist\Gears\ArrayType;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AppExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('controllers.yml');
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

        foreach ($config as $driver => $options) {
            foreach ($options['prefixes'] as $prefix) {
                $managerDef->addMethodCall('addPrefix', [$driver, $prefix]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'app';
    }
}
