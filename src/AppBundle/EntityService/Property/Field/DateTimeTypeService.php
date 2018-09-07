<?php

namespace AppGear\AppBundle\EntityService\Property\Field;

use AppGear\AppBundle\Form\FormFieldTypeServiceInterface;
use AppGear\AppBundle\View\ViewFieldInterface;
use AppGear\AppBundle\Storage\Platform\MysqlFieldTypeServiceInterface;
use DateTime;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class DateTimeTypeService implements FormFieldTypeServiceInterface, MysqlFieldTypeServiceInterface, ViewFieldInterface
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

    /**
     * {@inheritdoc}
     */
    public function getViewValue($value)
    {
        /** @var DateTime $value */
        if (is_a($value, DateTime::class)) {
            return $value->format('Y-m-d H:i:s');
        }

        return null;
    }
}