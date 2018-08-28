<?php

namespace AppGear\AppBundle\Form\Dto;

class FormSubmitResultDto
{
    /**
     * @var boolean
     */
    public $isSubmitted;

    /**
     * @var boolean
     */
    public $isValid;

    /**
     * @var string
     */
    public $errors;
}