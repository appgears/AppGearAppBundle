<?php

namespace AppGear\AppBundle\EntityService\Property\Field;

use AppGear\AppBundle\Form\FormFieldTypeServiceInterface;
use AppGear\AppBundle\Form\Type\MarkdownType;
use AppGear\AppBundle\Storage\Platform\MysqlFieldTypeServiceInterface;

class MarkdownTypeService implements FormFieldTypeServiceInterface, MysqlFieldTypeServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return MarkdownType::class;
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