<?php

namespace AppGear\AppBundle\Storage;

use AppGear\CoreBundle\Entity\Model;

abstract class DriverAbstract
{
    /**
     * Finds all objects in the repository.
     *
     * @param Model $model Model
     *
     * @return array The objects.
     */
    abstract public function findAll(Model $model);

    /**
     * Finds objects by a set of criteria.
     *
     * Optionally sorting and limiting details can be passed.
     *
     * @param Model      $model Model
     * @param array      $criteria
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return array The objects.
     */
    abstract public function findBy(Model $model, array $criteria, array $orderBy = null, $limit = null, $offset = null);

    /**
     * Finds an object by its primary key / identifier.
     *
     * @param Model $model Model
     * @param mixed $id    The identifier.
     *
     * @return object The object.
     */
    abstract public function find(Model $model, $id);

    /**
     * Finds entities by criteria expression.
     *
     * @param Model  $model     Model
     * @param string $expr      Expression language criteria string
     * @param array  $orderings The orderings
     *                          Keys are field and values are the order, being either ASC or DESC.
     *
     * @return array The objects.
     */
    abstract public function findByExpr(Model $model, $expr, array $orderings = []);

    /**
     * Save object to the storage.
     *
     * @param Model  $model  Model
     * @param object $object Object
     *
     * @return void
     */
    abstract public function save(Model $model, $object);

    /**
     * Remove object from the storage.
     *
     * @param Model  $model  Model
     * @param object $object Object
     *
     * @return void
     */
    abstract public function remove(Model $model, $object);

}
