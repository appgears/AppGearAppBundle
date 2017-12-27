<?php

namespace AppGear\AppBundle\Storage\Driver\Yaml;

use AppGear\CoreBundle\Entity\Property;
use AppGear\CoreBundle\Entity\Property\Field;
use AppGear\CoreBundle\Model\ModelManager;
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
     * @var ModelManager
     */
    private $modelManager;

    /**
     * Constructor.
     *
     * @param KernelInterface $kernel
     * @param ModelManager    $modelManager
     */
    public function __construct(KernelInterface $kernel, ModelManager $modelManager)
    {
        $this->kernel       = $kernel;
        $this->modelManager = $modelManager;
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

        $childrenModels = [$this->modelManager->get($model)] + $this->modelManager->children($model);

        foreach ($childrenModels as $childModel) {
            if (isset($configuration[$childModel->getName()][$id])) {
                return [$childModel->getName(), $configuration[$childModel->getName()][$id]];
            }
        }

        throw new RuntimeException(sprintf('Record #"%s" for model "%s" not found', $id, $model));
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
        return file_exists($bundlePath . '/Resources/config/appgear1.yml');
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

        return $yamlParser->parse(file_get_contents($bundlePath . '/Resources/config/appgear1.yml'));
    }
}