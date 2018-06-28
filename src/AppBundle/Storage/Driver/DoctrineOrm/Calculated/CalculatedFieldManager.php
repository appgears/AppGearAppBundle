<?php

namespace AppGear\AppBundle\Storage\Driver\DoctrineOrm\Calculated;

use AppGear\AppBundle\Helper\StorageHelper;
use AppGear\CoreBundle\Entity\Property;
use AppGear\CoreBundle\Helper\PropertyHelper;
use AppGear\CoreBundle\Model\ModelManager;

class CalculatedFieldManager
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * CalculatedFieldSubscriber constructor.
     *
     * @param ModelManager $modelManager
     */
    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    /**
     * Update entity fields in model for suitable model calculated properties
     *
     * @param object $object
     */
    public function update($object)
    {
        if (!$this->modelManager->isModel($object)) {
            return;
        }

        $model = $this->modelManager->getByInstance($object);

        /** @var Property $property */
        foreach ($model->getProperties() as $property) {
            if (!PropertyHelper::isCalculated($property)) {
                continue;
            }
            if (!StorageHelper::isManagedProperty($property)) {
                continue;
            }

            $updater = 'update' . ucfirst($property->getName());
            $object->$updater();
        }
    }
}