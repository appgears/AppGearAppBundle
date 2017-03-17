<?php

namespace AppGear\AppBundle\Storage;

use Cosmologist\Gears\StringType;

class DriverManager
{
    /**
     * Map between models (models prefixes) and suitable drivers
     *
     * @var array
     */
    private $prefixes = [];

    /**
     * Default driver
     *
     * @var DriverInterface
     */
    private $defaultDriver;

    /**
     * Constructor
     *
     * @param DriverInterface $defaultDriver Default driver
     */
    public function __construct(DriverInterface $defaultDriver = null)
    {
        $this->defaultDriver = $defaultDriver;
    }

    /**
     * Add prefix for driver
     *
     * @param DriverInterface $driver   Driver
     * @param string[]        $prefixes Model prefixes supported by driver
     *
     * @return $this
     */
    public function addDriver(DriverInterface $driver, array $prefixes)
    {
        $this->prefixes[] = (object) [
            'driver'   => $driver,
            'prefixes' => $prefixes
        ];

        return $this;
    }

    /**
     * Return driver for model
     *
     * @param string $modelName The model name
     *
     * @return DriverInterface
     */
    public function getDriver($modelName)
    {
        foreach ($this->prefixes as $item) {
            foreach ($item->prefixes as $prefix) {
                if (StringType::startsWith($modelName, $prefix)) {
                    return $item->driver;
                }
            }
        }

        return $this->defaultDriver;
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
