<?php

namespace AppGear\AppBundle\Storage;

use Symfony\Component\DependencyInjection\ContainerInterface;

class DriverManager
{
    /**
     * Map between models (models prefixes) and suitable drivers
     *
     * @var array
     */
    private $prefixes = [];

    /**
     * Container
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container Container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Add prefix for driver
     *
     * @param string $alias  Driver alias
     * @param string $prefix Class name prefix
     *
     * @return $this
     */
    public function addPrefix($alias, $prefix)
    {
        if (!array_key_exists($alias, $this->prefixes)) {
            $this->prefixes[$alias] = [];
        }
        $this->prefixes[$alias][] = $prefix;

        return $this;
    }

    /**
     * Return driver for model
     *
     * @param string $modelName The model name
     *
     * @return DriverAbstract
     */
    public function getDriver($modelName)
    {
        foreach ($this->prefixes as $driverAlias => $prefixes) {
            foreach ($prefixes as $prefix) {
                if ((strpos($modelName, $prefix) === 0) && (array_key_exists($driverAlias, $this->prefixes))) {
                    return $this->container->get($driverAlias);
                }
            }
        }

        throw new \RuntimeException(sprintf('Driver for model "%s" not found', $modelName));
    }

    /**
     * Return all prefixes under the driver
     *
     * @param string $driverAlias Driver alias
     *
     * @return array
     */
    public function getPrefixes($driverAlias)
    {
        if (array_key_exists($driverAlias, $this->prefixes)) {
            return $this->prefixes[$driverAlias];
        }

        return [];
    }
}
