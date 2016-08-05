<?php

namespace AppGear\AppBundle\Storage;

use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Model\ModelManager;
use Cosmologist\Gears\FileSystem;

abstract class ProxyManagerAbstract
{
    /**
     * Cache directory
     *
     * @var string
     */
    protected $cacheDir;

    /**
     * Model manager
     *
     * @var ModelManager
     */
    protected $manager;

    /**
     * Proxies
     *
     * @var array
     */
    private $proxies = [];

    /**
     * ProxyManager constructor.
     *
     * @param string       $cacheDir Cache directory
     * @param ModelManager $manager  Model manager
     */
    public function __construct($cacheDir, ModelManager $manager)
    {
        $this->cacheDir = $cacheDir;
        $this->manager  = $manager;
    }

    /**
     * Return proxy class fqcn for model
     *
     * @param Model $model Model
     *
     * @return mixed
     */
    public function getEntityProxyClass(Model $model)
    {
        $modelName = $model->getName();

        if (!array_key_exists($modelName, $this->proxies)) {
            $fqcn = $this->manager->fullClassName($modelName);
            $fqcn = 'AppGearStorageProxy\\' . $fqcn;
            $path = $this->buildProxyClassFilePath($fqcn);
            if (!file_exists($path)) {
                $this->buildProxyClassFile($model, $path);
            }
            require $path;
            $this->proxies[$modelName] = $fqcn;
        }

        return $this->proxies[$modelName];
    }

    /**
     * Build proxy file
     *
     * @param Model  $model Model
     * @param string $path  Path to class file
     *
     * @return void
     */
    abstract protected function buildProxyClassFile(Model $model, $path);

    /**
     * Build path to the proxy class file
     *
     * @param string $fqcn FQCN
     *
     * @return string
     */
    protected function buildProxyClassFilePath($fqcn)
    {
        return FileSystem::joinPaths([$this->cacheDir, FileSystem::normalizeSeparators($fqcn)]) . '.php';
    }
}
