<?php

namespace AppGear\AppBundle\EntityService\Property\Field;

use AppGear\AppBundle\Form\FormFieldTypeServiceInterface;
use AppGear\AppBundle\Storage\Platform\MysqlFieldTypeServiceInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class IntegerTypeService implements FormFieldTypeServiceInterface, MysqlFieldTypeServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return IntegerType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormOptions()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getMysqlFieldType()
    {
        return 'integer';
    }
}