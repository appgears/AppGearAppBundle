<?php

namespace AppGear\AppBundle\Storage;

use AppGear\AppBundle\Entity\Storage\Criteria;

interface DriverInterface
{
    /**
     * Finds all objects in the repository.
     *
     * @param string $model Model
     *
     * @return array The objects.
     */
    public function findAll($model);

    /**
     * Finds objects by a set of criteria.
     *
     * Optionally sorting and limiting details can be passed.
     *
     * @param string     $model Model
     * @param Criteria   $criteria
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return array The objects.
     */
    public function findBy($model, Criteria $criteria = null, array $orderBy = null, $limit = null, $offset = null);

    /**
     * Counts objects by a set of criteria.
     *
     * Optionally sorting and limiting details can be passed.
     *
     * @param string   $model Model
     * @param Criteria $criteria
     *
     * @return int Count
     */
    public function countBy($model, Criteria $criteria = null);

    /**
     * Finds an object by its primary key / identifier.
     *
     * @param string $model Model
     * @param mixed  $id    The identifier.
     *
     * @return object The object.
     */
    public function find($model, $id);

    /**
     * Finds entities by criteria expression.
     *
     * @param string   $model      Model
     * @param string   $expression Expression language criteria string
     * @param array    $orderBy    The orderings
     *                             Keys are field and values are the order, being either ASC or DESC.
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array The objects.
     */
    public function findByExpr($model, $expression, array $orderBy = null, $limit = null, $offset = null);

    /**
     * Counts entities by criteria expression.
     *
     * @param string $model      Model
     * @param string $expression Expression language criteria string
     *
     * @return int Count
     */
    public function countByExpr($model, $expression);

    /**
     * Save object to the storage.
     *
     * @param object $object Object
     *
     * @return void
     */
    public function save($object);

    /**
     * Remove object from the storage.
     *
     * @param object $object Object
     *
     * @return void
     */
    public function remove($object);

}
