<?php

namespace AppGear\AppBundle\Form;

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
