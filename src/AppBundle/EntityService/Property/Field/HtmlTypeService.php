<?php

namespace AppGear\AppBundle\EntityService\Property\Field;

use AppGear\AppBundle\Form\FormFieldTypeServiceInterface;
use AppGear\AppBundle\Form\Type\HtmlType;

class HtmlTypeService implements FormFieldTypeServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return HtmlType::class;
    }
}