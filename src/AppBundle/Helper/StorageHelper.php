<?php

namespace AppGear\AppBundle\Helper;

use AppGear\AppBundle\Entity\Storage\Column;
use AppGear\CoreBundle\Entity\Property\Relationship;
use AppGear\CoreBundle\EntityService\PropertyService;
use AppGear\CoreBundle\Helper\ModelHelper;

class StorageHelper
{
    public static function getBacksideProperty(Relationship $relationship)
    {
        $extension = (new PropertyService($relationship))->getExtension(Column::class);

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
}