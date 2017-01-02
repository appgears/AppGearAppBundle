<?php

namespace AppGear\AppBundle\Storage;

use AppGear\CoreBundle\Model\ModelManager;

class Repository
{
    /**
     * Model
     *
     * @var string
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
     * @param string         $model        Model
     * @param ModelManager   $modelManager Model manager
     */
    public function __construct(DriverAbstract $driver, $model, ModelManager $modelManager)
    {
        $this->model        = $model;
        $this->driver       = $driver;
        $this->modelManager = $modelManager;
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
     * Finds objects by a set of criteria.
     *
     * Optionally sorting and limiting details can be passed.
     *
     * @param array      $criteria
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return array The objects.
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->driver->findBy($this->model, $criteria, $orderBy, $limit, $offset);
    }

    /**
     * Finds entities by criteria expression.
     *
     * @param string $expr      Expression language criteria string
     * @param array  $orderings The orderings
     *                          Keys are field and values are the order, being either ASC or DESC.
     *
     * @return array The objects.
     */
    public function findByExpr($expr, array $orderings = [])
    {
        $entities = $this->driver->findByExpr($this->model, $expr, $orderings);
        foreach ($entities as $entity) {
            $this->modelManager->injectServices($this->model->getName(), $entity);
        }

        return $entities;
    }

    /**
     * Finds a single object by a criteria expression.
     *
     * @param string $expr      Expression language criteria string
     * @param array  $orderings The orderings
     *                          Keys are field and values are the order, being either ASC or DESC.
     *
     * @return object|null The object or null.
     */
    public function findOneByExpr($expr, array $orderings = [])
    {
        $entities = $this->findByExpr($expr, $orderings);
        if (count($entities) > 0) {
            return $entities[0];
        }

        return null;
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
        $this->driver->save($object);
    }

    /**
     * Remove object from the storage
     *
     * @param object $object Object
     *
     * @return void
     */
    public function remove($object)
    {
        $this->driver->remove($object);
    }
}
