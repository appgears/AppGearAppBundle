<?php

namespace AppGear\AppBundle\Storage\Driver\Yaml;

use AppGear\AppBundle\Storage\DriverInterface;

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
     * Constructor.
     *
     * @param Loader          $loader          Loader
     * @param HydratorFactory $hydratorFactory Hydrator factory
     */
    public function __construct(Loader $loader, HydratorFactory $hydratorFactory)
    {
        $this->loader          = $loader;
        $this->hydratorFactory = $hydratorFactory;
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
    public function countBy($model, array $criteria)
    {
        throw new \RuntimeException('Not implemented yet');
    }

    /**
     * {@inheritdoc}
     */
    public function find($model, $id)
    {
        list($model, $data) = $this->loader->get($model, $id);

        return $this->hydratorFactory->get($model)->hydrate($model, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function findByExpr($model, $expression, array $orderBy = null, $limit = null, $offset = null)
    {
        throw new \RuntimeException('Not implemented yet');
    }

    /**
     * {@inheritdoc}
     */
    public function countByExpr($model, $expression)
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