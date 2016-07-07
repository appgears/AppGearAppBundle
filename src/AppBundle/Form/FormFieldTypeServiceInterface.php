<?php

namespace AppGear\AppBundle\Form;

interface FormFieldTypeServiceInterface
{
    /**
     * Return field form type class
     *
     * @return string
     */
    public function getFormType();
}
