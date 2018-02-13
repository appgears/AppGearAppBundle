<?php

namespace AppGear\AppBundle\Helper;

use AppGear\AppBundle\Entity\Storage\Column;
use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Entity\Property;
use AppGear\CoreBundle\Entity\Property\Relationship;
use AppGear\CoreBundle\Helper\ModelHelper;
use AppGear\CoreBundle\Helper\PropertyHelper;

class StorageHelper
{
    public static function getBacksideProperty(Relationship $relationship)
    {
        $extension = PropertyHelper::getExtension($relationship, Column::class);

        if (null === $extension) {
            return null;
        }

        if (strlen($mappedBy = $extension->getMappedBy())) {
            return ModelHelper::getProperty($relationship->getTarget(), $mappedBy);
        }
        if (strlen($inversedBy = $extension->getInversedBy())) {
            return ModelHelper::getProperty($relationship->getTarget(), $inversedBy);
        }

        return null;
    }

    /**
     * Get entity identifier value
     *
     * @param Model $model Model
     *
     * @return Property|null
     */
    public static function getIdentifierProperty(Model $model): ?Property
    {
        if (null !== $result = ModelHelper::getPropertyWithExtension($model, Column::class)) {
            return $result->property;
        }

        return null;
    }
}