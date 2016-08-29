<?php

namespace AppGear\AppBundle\Storage;

use AppGear\AppBundle\Storage\Driver\DoctrinePhpCr;
use AppGear\CoreBundle\Entity\Model;
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
        if (is_string($model)) {
            $model = $this->modelManager->get($model);
        }

        if (array_key_exists($model->getName(), $this->repositories)) {
            return $this->repositories[$model->getName()];
        }
        
        $driver                                = $this->driverManager->getDriver($model->getName());
        $repository                            = new Repository($driver, $model, $this->modelManager);
        $this->repositories[$model->getName()] = $repository;

        return $repository;
    }
}
