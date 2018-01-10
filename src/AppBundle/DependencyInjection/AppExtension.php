<?php

namespace AppGear\AppBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AppExtension extends Extension
{
    public $driversMap = [
        'doctrine-orm' => 'appgear.storage.driver.doctrine_orm'
    ];

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
        $loader->load('security.yml');
        $loader->load('services.yml');
        $loader->load('storage.yml');

        $container->setParameter('appgear.application.upload.directory', $config['upload']['directory']);
        $container->setParameter('appgear.application.upload.file_prefix', $config['upload']['file_prefix']);

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

        if (null !== $config['default_driver']) {
            $managerDef->addArgument(new Reference($config['default_driver']));
        }

        foreach ($config['drivers'] as $name => $options) {
            $managerDef->addMethodCall('addDriver', [new Reference($this->driversMap[$options['type']]), $options['prefixes']]);
        }

        // For AppGearModelDriverCompilerPass
        $container->setParameter('appgear.storage.drivers', $config['drivers']);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'app';
    }
}
