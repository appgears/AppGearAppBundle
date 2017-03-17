<?php

namespace AppGear\AppBundle\Storage\Driver\Yaml;

use AppGear\AppBundle\Storage\DriverInterface;
use AppGear\CoreBundle\Entity\Property;
use AppGear\CoreBundle\Entity\Property\Field;
use AppGear\CoreBundle\Model\ModelManager;

class Driver implements DriverInterface
{
    /**
     * Loader
     *
     * @var Loader
     */
    private $loader;

    /** Hydrator factory
     *
     * @var HydratorFactory
     */
    private $hydratorFactory;

    /**
     * Model manager
     *
     * @var ModelManager
     */
    private $modelManager;

    /**
     * Constructor.
     *
     * @param Loader          $loader          Loader
     * @param HydratorFactory $hydratorFactory Hydrator factory
     * @param ModelManager    $modelManager    Model manager
     */
    public function __construct(Loader $loader, HydratorFactory $hydratorFactory, ModelManager $modelManager)
    {
        $this->loader          = $loader;
        $this->hydratorFactory = $hydratorFactory;
        $this->modelManager    = $modelManager;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll($model)
    {
        throw new \RuntimeException('Not implemented yet');
    }

    /**
     * {@inheritdoc}
     */
    public function findBy($model, array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        throw new \RuntimeException('Not implemented yet');
    }

    /**
     * {@inheritdoc}
     */
    public function find($model, $id)
    {
        return $this->hydratorFactory->get($model)->hydrate(
            $this->modelManager->instance($model),
            $this->loader->get($model, $id)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function findByExpr($model, $expr, array $orderings = [])
    {
        throw new \RuntimeException('Not implemented yet');
    }

    /**
     * {@inheritdoc}
     */
    public function save($object)
    {
        throw new \RuntimeException('Not implemented yet');
    }

    /**
     * {@inheritdoc}
     */
    public function remove($object)
    {
        throw new \RuntimeException('Not implemented yet');
    }
}