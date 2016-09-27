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

    /**
     * Return field form type options
     *
     * @return array
     */
    public function getFormOptions();
}
