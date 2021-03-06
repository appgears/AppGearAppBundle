<?php

namespace AppGear\AppBundle\Storage;

use AppGear\AppBundle\Entity\Storage\Column;
use AppGear\AppBundle\Helper\StorageHelper;
use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Entity\Property;
use AppGear\CoreBundle\Helper\ModelHelper;
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

    /**
     * Save object to the storage.
     *
     * @param object $entity Entity
     *
     * @return void
     */
    public function save($entity)
    {
        $model = $this->modelManager->getByInstance($entity);
        $this->getRepository($model)->save($entity);
    }

    /**
     * Remove entity from the storage
     *
     * @param object $entity Entity
     *
     * @return void
     */
    public function remove($entity)
    {
        $model = $this->modelManager->getByInstance($entity);
        $this->getRepository($model)->remove($entity);
    }

    /**
     * Get entity identifier value
     *
     * @param object $entity Entity
     *
     * @return Property|null
     */
    public function getIdentifierProperty($entity): ?Property
    {
        $model = $this->modelManager->getByInstance($entity);

        return StorageHelper::getIdentifierProperty($model);
    }

    /**
     * Get entity identifier value
     *
     * @param object $entity Entity
     *
     * @return mixed
     */
    public function getIdentifierValue($entity)
    {
        return ModelHelper::readPropertyValue($entity, $this->getIdentifierProperty($entity));
    }
}
