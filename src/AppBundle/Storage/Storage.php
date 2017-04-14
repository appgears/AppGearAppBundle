<?php

namespace AppGear\AppBundle\Storage;

use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Model\ModelManager;

class Storage
{
    /**
     * Storage drivers manager
     *
     * @var DriverManager
     */
    protected $driverManager;

    /**
     * Models manager
     *
     * @var ModelManager
     */
    protected $modelManager;

    /**
     * Repositories cache
     *
     * @var Repository[]
     */
    protected $repositories = [];

    /**
     * CrudController constructor.
     *
     * @param DriverManager $driverManager Drivers manager
     * @param ModelManager  $modelManager  Models manager
     */
    public function __construct(DriverManager $driverManager, ModelManager $modelManager)
    {
        $this->driverManager = $driverManager;
        $this->modelManager  = $modelManager;
    }

    /**
     * Gets the repository for a model
     *
     * @param string|Model $model Model instance or model ID
     *
     * @return Repository
     */
    public function getRepository($model)
    {
        $model = (string) $model;

        if (array_key_exists($model, $this->repositories)) {
            return $this->repositories[$model];
        }

        $driver                     = $this->driverManager->getDriver($model);
        $repository                 = new Repository($driver, $model, $this->modelManager);
        $this->repositories[$model] = $repository;

        return $repository;
    }

    /**
     * Finds an object by its primary key/identifier.
     *
     * @param string|Model $model Model
     * @param mixed        $id    ID
     *
     * @return object
     */
    public function find($model, $id)
    {
        return $this->getRepository($model)->find($id);
    }
}
