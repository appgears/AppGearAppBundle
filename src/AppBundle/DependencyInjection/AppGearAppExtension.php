<?php

namespace AppGear\AppBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
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
    }
}
