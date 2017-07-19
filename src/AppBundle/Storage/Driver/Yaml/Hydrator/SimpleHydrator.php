<?php
/**
 * Created by PhpStorm.
 * User: pavellevin
 * Date: 20.01.17
 * Time: 17:23
 */

namespace AppGear\AppBundle\Storage\Driver\Yaml\Hydrator;

use AppGear\AppBundle\Storage\Driver\Yaml\Hydrator;

class SimpleHydrator implements Hydrator
{
    /**
     * {@inheritdoc}
     */
    public function hydrate($instance, array $data)
    {
        return $instance;
    }
}