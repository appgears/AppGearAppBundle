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
     * @param ModelManager   $manager Models manager
     * @param DriverAbstract $driver  Storage driver
     */
    public function __construct(ModelManager $manager, DriverAbstract $driver)
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

        $repository                            = new Repository($model, $this->driver);
        $this->repositories[$model->getName()] = $repository;

        return $repository;
    }
}
