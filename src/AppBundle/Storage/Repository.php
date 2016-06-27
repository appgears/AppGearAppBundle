<?php

namespace AppGear\AppBundle\Storage;

use AppGear\CoreBundle\Entity\Model;

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
     * CrudController constructor.
     *
     * @param Model          $model  Model
     * @param DriverAbstract $driver Storage driver
     */
    public function __construct(Model $model, DriverAbstract $driver)
    {
        $this->model  = $model;
        $this->driver = $driver;
    }

    /**
     * Finds all objects in the repository.
     *
     * @return array The objects.
     */
    public function findAll()
    {
        return $this->driver->findAll($this->model);
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
        return $this->driver->find($this->model, $id);
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
        return $this->driver->findByExpr($this->model, $expr);
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
