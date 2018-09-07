<?php

namespace AppGear\AppBundle\View;

interface ViewFieldInterface
{
    /**
     * Returns field value for view
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function getViewValue($value);
}
