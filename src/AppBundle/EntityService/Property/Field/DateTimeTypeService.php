<?php

namespace AppGear\AppBundle\EntityService\Property\Field;

use AppGear\AppBundle\Form\FormFieldTypeServiceInterface;
use AppGear\AppBundle\Storage\Platform\MysqlFieldTypeServiceInterface;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class DateTimeTypeService implements FormFieldTypeServiceInterface, MysqlFieldTypeServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return DateTimeType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormOptions()
    {
        return [
            'widget' => 'single_text',
            'format' => 'yyyy-MM-dd HH:mm:ss'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getMysqlFieldType()
    {
        return 'datetime';
    }
}