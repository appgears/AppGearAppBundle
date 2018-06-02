<?php

namespace AppGear\AppBundle\Helper;

use AppGear\AppBundle\Entity\Storage\Column;
use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Entity\Property;
use AppGear\CoreBundle\Helper\ModelHelper;
use AppGear\CoreBundle\Helper\PropertyHelper;

class StorageHelper
{
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

    /**
     * Is identifier field
     *
     * @param Property $property
     *
     * @return boolean
     */
    public static function isIdentifierProperty(Property $property)
    {
        /** @var Column $columnExtension */
        $columnExtension = PropertyHelper::getExtension($property, Column::class);
        if ($columnExtension !== null && $columnExtension->getIdentifier()) {
            return true;
        }

        return false;
    }

    /**
     * Returns backside property for relationship
     *
     * @param Property $property
     *
     * @return Property\Relationship|null
     */
    public static function getBacksideProperty(Property $property)
    {
        if (!PropertyHelper::isRelationship($property)) {
            return null;
        }
        /** @var Property\Relationship $property */

        /** @var Column $extension */
        $extension = PropertyHelper::getExtension($property, Column::class);

        if (null === $extension) {
            return null;
        }

        if (strlen($mappedBy = $extension->getMappedBy()) > 0) {
            return ModelHelper::getRelationship($property->getTarget(), $mappedBy);
        }
        if (strlen($inversedBy = $extension->getInversedBy()) > 0) {
            return ModelHelper::getRelationship($property->getTarget(), $inversedBy);
        }

        return null;
    }

    /**
     * Checks if property is related to backside relationship
     *
     * @param Property              $property
     * @param Property\Relationship $backside
     *
     * @return bool
     */
    public static function isRelatedProperty(Property $property, Property\Relationship $backside)
    {
        return self::getBacksideProperty($property) === $backside;
    }
}