<?php

namespace AppGear\AppBundle;

use AppGear\AppBundle\DependencyInjection\Module\RoutingsConfigurator;
use AppGear\CoreBundle\DependencyInjection\AppGearExtension;
use AppGear\CoreBundle\DependencyInjection\Configuration;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AppGearAppBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        AppGearExtension::$moduleConfigurators[] = Configuration::$moduleConfigurators[] = new RoutingsConfigurator();

        parent::build($container);
    }
}
