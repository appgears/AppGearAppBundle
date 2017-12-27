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
     * @param string $model Model name
     * @param array  $data  Data
     *
     * @return object Instance
     */
    public function hydrate(string $model, array $data);
}