<?php

namespace AppGear\AppBundle\Storage;

class DriverManager
{
    /**
     * Map between classes (class prefixes) and suitable drivers
     *
     * @var array
     */
    private $prefixes = [];

    /**
     * Drivers
     *
     * @var array
     */
    private $drivers = [];

    /**
     * Add prefix for driver
     *
     * @param string $alias  Driver alias
     * @param string $prefix Class name prefix
     *
     * @return $this
     */
    public function addDriverPrefix($alias, $prefix)
    {
        if (!array_key_exists($alias, $this->prefixes)) {
            $this->prefixes[$alias] = [];
        }
        $this->prefixes[$alias][] = $prefix;

        return $this;
    }

    /**
     * Add driver
     *
     * @param string         $alias  Driver alias
     * @param DriverAbstract $driver Driver
     *
     * @return $this
     */
    public function addDriver($alias, DriverAbstract $driver)
    {
        $this->drivers[$alias] = $driver;

        return $this;
    }

    /**
     * Return driver for classes with passed prefix
     *
     * @param string $fqcn FQCN
     *
     * @return DriverAbstract
     */
    public function getDriver($fqcn)
    {
        foreach ($this->prefixes as $driverAlias => $prefixes) {
            foreach ($prefixes as $prefix) {
                if ((strpos($fqcn, $prefix) === 0) && (array_key_exists($driverAlias, $this->drivers))) {
                    return $this->drivers[$driverAlias];
                }
            }
        }

        throw new \RuntimeException(sprintf('Driver for class "%" not found', $fqcn));
    }
}
