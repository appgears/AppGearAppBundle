<?php

namespace AppGear\AppBundle\EntityService\Property\Field;

use AppGear\AppBundle\Form\FormFieldTypeServiceInterface;
use AppGear\AppBundle\Form\Type\MarkdownType;
use AppGear\AppBundle\Storage\Platform\MysqlFieldTypeServiceInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class FileTypeService implements FormFieldTypeServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return FileType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormOptions()
    {
        return [];
    }
}