<?php

namespace AppGear\AppBundle\Storage;

use AppGear\AppBundle\Storage\Driver\DoctrinePhpCr;
use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Model\ModelManager;

class Storage
{
    /**
     * Storage driver
     *
     * @var Driver
     */
    protected $driver;

    /**
     * Models manager
     *
     * @var ModelManager
     */
    protected $manager;

    /**
     * Repositories cache
     *
     * @var Repository[]
     */
    protected $repositories = [];

    /**
     * CrudController constructor.
     *
     * @param DriverAbstract $driver  Storage driver
     * @param ModelManager   $manager Models manager
     */
    public function __construct(DriverAbstract $driver, ModelManager $manager)
    {
        $this->driver  = $driver;
        $this->manager = $manager;
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
        if (is_string($model)) {
            $model = $this->manager->get($model);
        }

        if (array_key_exists($model->getName(), $this->repositories)) {
            return $this->repositories[$model->getName()];
        }

        $repository                            = new Repository($this->driver, $model, $this->manager);
        $this->repositories[$model->getName()] = $repository;

        return $repository;
    }
}
