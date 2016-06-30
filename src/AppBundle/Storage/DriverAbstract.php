<?php

namespace AppGear\AppBundle\Storage;

use AppGear\CoreBundle\Entity\Model;

abstract class DriverAbstract
{
    /**
     * Tells the ObjectManager to make an instance managed and persistent.
     *
     * The object will be entered into the database as a result of the flush operation.
     *
     * NOTE: The persist operation always considers objects that are not yet known to
     * this ObjectManager as NEW. Do not pass detached objects to the persist operation.
     *
     * @param object $object The instance to make managed and persistent.
     *
     * @return void
     */

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
     * Finds all objects in the repository.
     *
     * @param Model $model Model
     *
     * @return array The objects.
     */
    abstract public function findAll(Model $model);

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
     * @param Model  $model Model
     * @param string $expr  Expression language criteria string
     *
     * @return array The objects.
     */
    abstract public function findByExpr(Model $model, $expr);
}
