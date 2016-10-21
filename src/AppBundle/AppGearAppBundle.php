<?php

namespace AppGear\AppBundle;

use AppGear\AppBundle\DependencyInjection\AppExtension;
use AppGear\AppBundle\DependencyInjection\Compiler\AppGearModelDriverCompilerPass;
use AppGear\AppBundle\DependencyInjection\Module\RoutingsConfigurator;
use AppGear\CoreBundle\DependencyInjection\CoreExtension;
use AppGear\CoreBundle\DependencyInjection\Configuration;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AppGearAppBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'app';

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        CoreExtension::$moduleConfigurators[] = Configuration::$moduleConfigurators[] = new RoutingsConfigurator();

        $container->addCompilerPass(new AppGearModelDriverCompilerPass());

        parent::build($container);
    }

    /**
     * Override method for using "app" section name in the config
     *
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new AppExtension();
        }

        return $this->extension;
    }
}
