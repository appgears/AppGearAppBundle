<?php

namespace AppGear\AppBundle\View\Introspection;

interface IntrospectionInterface
{
    /**
     * Introspect for target fields
     *
     * @param mixed $target
     *
     * @return mixed
     */
    public function introspect($target);
}