<?php
/**
 * Created by PhpStorm.
 * User: pavellevin
 * Date: 20.01.17
 * Time: 17:23
 */

namespace AppGear\AppBundle\Storage\Driver\Yaml;

interface Hydrator
{
    /**
     * Hydrates data for single instance
     *
     * @param object $instance Instance
     * @param array  $data     Data
     *
     * @return object Instance
     */
    public function hydrate($instance, array $data);
}