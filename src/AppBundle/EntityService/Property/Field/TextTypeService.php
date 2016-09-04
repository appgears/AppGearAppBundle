<?php

namespace AppGear\AppBundle\EntityService\Property\Field;

use AppGear\AppBundle\Form\FormFieldTypeServiceInterface;
use AppGear\AppBundle\Storage\Platform\MysqlFieldTypeServiceInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class TextTypeService implements FormFieldTypeServiceInterface, MysqlFieldTypeServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return TextareaType::class;
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
        return 'text';
    }
}