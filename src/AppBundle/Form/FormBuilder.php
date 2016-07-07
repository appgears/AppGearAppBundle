<?php

namespace AppGear\AppBundle\Form;

use AppGear\CoreBundle\Entity\Model;
use Symfony\Component\Form\Form;

class FormBuilder
{
    /**
     * Build form for model
     *
     * @param Model  $model  Model
     * @param object $entity Model entity
     *
     * @return Form
     */
    protected function build(Model $model, $entity = null)
    {

    }
}