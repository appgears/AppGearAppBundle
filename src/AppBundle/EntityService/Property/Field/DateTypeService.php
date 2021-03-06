<?php

namespace AppGear\AppBundle\EntityService\Property\Field;

use AppGear\AppBundle\Form\FormFieldTypeServiceInterface;
use AppGear\AppBundle\Storage\Platform\MysqlFieldTypeServiceInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class DateTypeService implements FormFieldTypeServiceInterface, MysqlFieldTypeServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return DateType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormOptions()
    {
        return [
            'widget' => 'single_text',
            'format' => 'yyyy-MM-dd'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getMysqlFieldType()
    {
        return 'date';
    }
}