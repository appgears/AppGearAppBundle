<?php

namespace AppGear\AppBundle\Storage\Driver\Yaml;

use AppGear\CoreBundle\Entity\Property;
use AppGear\CoreBundle\Entity\Property\Field;
use Symfony\Component\Yaml\Parser;

class Loader
{
    /**
     * All registered bundles
     *
     * @var array
     */
    private $bundles;

    /**
     * Constructor.
     *
     * @param array $bundles All registered bundles
     */
    public function __construct($bundles)
    {
        $this->bundles = $bundles;

        $this->load();
    }

    /**
     * Load all configurations
     * 
     * @return array
     */
    protected function load()
    {
        $configuration = [];

        foreach ($this->bundles as $bundle) {
            if ($this->hasConfiguration($bundle->getPath())) {
                $configuration = array_merge_recursive(
                    $configuration,
                    $this->readConfiguration($bundle->getPath())
                );
            }
        }

        return $configuration;
    }

    /**
     * Check if bundle has configuration
     *
     * @param string $bundlePath Bundle path
     *
     * @return bool
     */
    protected function hasConfiguration($bundlePath)
    {
        return file_exists($bundlePath . '/Resources/config/appgear.yml');
    }

    /**
     * Read configuration from bundle
     *
     * @param string $bundlePath Bundle path
     *
     * @return mixed
     */
    protected function readConfiguration($bundlePath)
    {
        $yamlParser = new Parser();
        return $yamlParser->parse(file_get_contents($bundlePath . '/Resources/config/datagrid/%s.yml'));
    }
}