<?php

namespace AppGear\AppBundle\Storage;

use AppGear\AppBundle\Entity\Storage\Criteria;
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
     * @var DriverInterface
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
     * @param DriverInterface $driver       Storage driver
     * @param string          $model        Model
     * @param ModelManager    $modelManager Model manager
     */
    public function __construct(DriverInterface $driver, $model, ModelManager $modelManager)
    {
        $this->model        = $model;
        $this->driver       = $driver;
        $this->modelManager = $modelManager;
    }

    /**
     * Finds an object by its primary key/identifier.
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
     * Finds all objects in the repository.
     *
     * @return array The objects.
     */
    public function findAll()
    {
        return $this->driver->findAll($this->model);
    }

    /**
     * Finds objects by a set of criteria.
     *
     * Optionally sorting and limiting details can be passed.
     *
     * @param Criteria   $criteria
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return array The objects.
     */
    public function findBy(Criteria $criteria = null, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->driver->findBy($this->model, $criteria, $orderBy, $limit, $offset);
    }

    /**
     * Counts objects by a set of criteria.
     *
     * Optionally sorting and limiting details can be passed.
     *
     * @param Criteria $criteria
     *
     * @return int Count
     */
    public function countBy(Criteria $criteria = null)
    {
        return $this->driver->countBy($this->model, $criteria);
    }

    /**
     * Finds a single object by a set of criteria.
     *
     * Optionally sorting and limiting details can be passed.
     *
     * @param Criteria   $criteria
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return array The objects.
     */
    public function findOneBy(Criteria $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $entities = $this->findBy($criteria, $orderBy, $limit, $offset);

        if (count($entities) > 0) {
            return $entities[0];
        }

        return null;
    }

    /**
     * Finds entities by criteria expression.
     *
     * @param string   $expr    Expression language criteria string
     * @param array    $orderBy The orderings
     *                          Keys are field and values are the order, being either ASC or DESC.
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array The objects.
     */
    public function findByExpr($expr, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->driver->findByExpr($this->model, $expr, $orderBy, $limit, $offset);
    }

    /**
     * Entities count by criteria expression.
     *
     * @param string $expr Expression language criteria string
     *
     * @return int Count
     */
    public function countByExpr($expr)
    {
        return $this->driver->countByExpr($this->model, $expr);
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

    public function remove($object)
    {
        $this->driver->remove($object);
    }
}
