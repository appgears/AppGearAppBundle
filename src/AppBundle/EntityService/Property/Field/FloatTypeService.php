<?php

namespace AppGear\AppBundle\EntityService\Property\Field;

use AppGear\AppBundle\Storage\Platform\MysqlFieldTypeServiceInterface;

class FloatTypeService implements MysqlFieldTypeServiceInterface
{/**
     * {@inheritdoc}
     */
    public function getMysqlFieldType()
    {
        return 'float';
    }
}