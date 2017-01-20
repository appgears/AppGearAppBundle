<?php
/**
 * Created by PhpStorm.
 * User: pavellevin
 * Date: 20.01.17
 * Time: 17:17
 */

namespace AppGear\AppBundle\Storage\Driver\Yaml;

class HydratorFactory
{
    /**
     * Hydrator registry
     *
     * @var Hydrator[]
     */
    protected $registry;

    /**
     * Default hydrator
     *
     * @var Hydrator
     */
    private $defaultHydrator;

    /**
     * Constructor
     *
     * @param Hydrator $defaultHydrator
     */
    public function __construct(Hydrator $defaultHydrator)
    {
        $this->defaultHydrator = $defaultHydrator;
    }

    /**
     * Add hydrator for specific model
     *
     * @param string   $model    Model name
     * @param Hydrator $hydrator Hydrator
     */
    public function register($model, Hydrator $hydrator)
    {
        $this->registry[$model] = $hydrator;
    }

    /**
     * Get hydrator for model
     *
     * @param string $model Model name
     *
     * @return Hydrator Hydrator
     */
    public function get($model)
    {
        if (!array_key_exists($model, $this->registry)) {
            return $this->defaultHydrator;
        }

        return $this->registry[$model];
    }
}