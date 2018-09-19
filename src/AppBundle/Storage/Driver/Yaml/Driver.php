<?php

namespace AppGear\AppBundle\Storage\Driver\Yaml;

use AppGear\AppBundle\Entity\Storage\Criteria;
use AppGear\AppBundle\Storage\DriverInterface;
use Symfony\Component\DependencyInjection\Container;

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
     * @var Container
     */
    private $container;

    /**
     * Constructor.
     *
     * @param Loader          $loader          Loader
     * @param HydratorFactory $hydratorFactory Hydrator factory
     */
    public function __construct(Container $container, Loader $loader, HydratorFactory $hydratorFactory)
    {
        $this->loader          = $loader;
        $this->hydratorFactory = $hydratorFactory;
        $this->container = $container;
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
    public function findBy($model, Criteria $criteria = null, array $orderBy = null, $limit = null, $offset = null)
    {
        throw new \RuntimeException('Not implemented yet');
    }

    /**
     * {@inheritdoc}
     */
    public function countBy($model, Criteria $criteria = null)
    {
        throw new \RuntimeException('Not implemented yet');
    }

    /**
     * {@inheritdoc}
     */
    public function find($model, $id)
    {
        list($model, $data) = $this->loader->get($model, $id);
        $data = $this->container->getParameterBag()->resolveValue($data);

        return $this->hydratorFactory->get($model)->hydrate($model, $data);
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