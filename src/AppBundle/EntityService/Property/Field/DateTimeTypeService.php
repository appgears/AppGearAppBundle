<?php

namespace AppGear\AppBundle\EntityService\Property\Field;

use AppGear\AppBundle\Form\FormFieldTypeServiceInterface;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class DateTimeTypeService implements FormFieldTypeServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return DateTimeType::class;
    }
}