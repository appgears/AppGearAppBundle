<?php

namespace AppGear\AppBundle\Storage;

use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Model\ModelManager;

class Repository
{
    /**
     * Model
     *
     * @var Model
     */
    private $model;

    /**
     * Storage driver
     *
     * @var DriverAbstract
     */
    private $driver;

    /**
     * Model manager
     *
     * @var ModelManager
     */
    private $modelManager;

    /**
     * CrudController constructor.
     *
     * @param DriverAbstract $driver       Storage driver
     * @param Model          $model        Model
     * @param ModelManager   $modelManager Model manager
     */
    public function __construct(DriverAbstract $driver, Model $model, ModelManager $modelManager)
    {
        $this->model = $model;
        $this->driver = $driver;
        $this->modelManager = $modelManager;
    }

    /**
     * Finds all objects in the repository.
     *
     * @return array The objects.
     */
    public function findAll()
    {
        $entities = $this->driver->findAll($this->model);
        foreach ($entities as $entity) {
            $this->modelManager->injectServices($this->model->getName(), $entity);
        }

        return $entities;
    }

    /**
     * Finds an object by its primary key / identifier.
     *
     * @param mixed $id The identifier.
     *
     * @return object The object.
     */
    public function find($id)
    {
        $entity = $this->driver->find($this->model, $id);
        return $this->modelManager->injectServices($this->model->getName(), $entity);
    }

    /**
     * Finds a single object by a criteria.
     *
     * @param string $field Field name
     * @param mixed  $value Field value
     *
     * @return object|null The object or null.
     */
    public function findOneBy($field, $value)
    {
        $entity = $this->driver->findOneBy($this->model, $field, $value);
        return $this->modelManager->injectServices($this->model->getName(), $entity);
    }

    /**
     * Finds entities by criteria expression.
     *
     * @param string $expr Expression language criteria string
     *
     * @return array The objects.
     */
    public function findByExpr($expr)
    {
        $entities = $this->driver->findByExpr($this->model, $expr);
        foreach ($entities as $entity) {
            $this->modelManager->injectServices($this->model->getName(), $entity);
        }

        return $entities;
    }

    /**
     * Save object to the storage.
     *
     * @param object $object Object
     *
     * @return void
     */
    public function save($object)
    {
        $this->driver->save($this->model, $object);
    }
}
