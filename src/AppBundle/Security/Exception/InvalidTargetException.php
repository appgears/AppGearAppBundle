<?php

namespace AppGear\AppBundle\Security\Exception;

use RuntimeException;

class InvalidTargetException extends RuntimeException
{
    public function __construct(string $type)
    {
        parent::__construct("Invalid security target type: $type");
    }

}