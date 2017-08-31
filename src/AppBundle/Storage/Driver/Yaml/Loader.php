<?php

namespace AppGear\AppBundle\Storage\Driver\Yaml;

use AppGear\CoreBundle\Entity\Property;
use AppGear\CoreBundle\Entity\Property\Field;
use RuntimeException;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Parser;

/**
 * Loader for model data
 */
class Loader
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * All registered bundles
     *
     * @var array
     */
    private $bundles;

    /**
     * Constructor.
     *
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel  = $kernel;
        //$this->bundles = $bundles;

        //$this->load();
    }

    /**
     * Return all model data
     *
     * @return array|mixed
     */
    public function all()
    {
        return $this->load();
    }

    /**
     * Return all model data or data with specific ID
     *
     * @param string $model Model name
     * @param mixed  $id    Model data ID
     *
     * @return array
     */
    public function get($model, $id)
    {
        $configuration = $this->load();

        // TODO: temp BC
        if ($model === 'core.model') {
            $model = 'models';
        }

        if (!array_key_exists($model, $configuration)) {
            return [];
        }

        if (!isset($configuration[$model][$id])) {
            throw new RuntimeException(sprintf('Record #"%s" for model "%s" not found', $id, $model));
        }

        return $configuration[$model][$id];
    }

    /**
     * Load all configurations
     *
     * @return array
     */
    protected function load()
    {
        $configuration = [];

        foreach ($this->kernel->getBundles() as $bundle) {
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

        return $yamlParser->parse(file_get_contents($bundlePath . '/Resources/config/appgear.yml'));
    }
}