<?php

namespace AppGear\AppBundle\Storage\Driver\DoctrineOrm\Calculated;

use AppGear\AppBundle\Entity\Storage\Column;
use AppGear\CoreBundle\Entity\Property;
use AppGear\CoreBundle\Helper\ModelHelper;
use AppGear\CoreBundle\Helper\PropertyHelper;
use AppGear\CoreBundle\Model\ModelManager;
use Cosmologist\Gears\ObjectType;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

/**
 * Listen to prePersist and preUpdate events and update entity fields in model for suitable model calculated properties.
 *
 * It is needed, because Doctrine does not use getters/setters and read/write entity data directly from object fields.
 */
class CalculatedFieldSubscriber implements EventSubscriber
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
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'prePersist',
            'preUpdate',
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->updateCalculateFields($args->getObject());
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->updateCalculateFields($args->getObject());
    }

    /**
     * update entity fields in model for suitable model calculated properties
     *
     * @param object $object
     */
    private function updateCalculateFields($object)
    {
        $model = $this->modelManager->getByInstance($object);

        /** @var Property $property */
        foreach ($model->getProperties() as $property) {
            if ($property->getCalculated() === null) {
                continue;
            }
            /** @var Column $columnExtension */
            if (null === $columnExtension = PropertyHelper::getExtension($property, Column::class)) {
                continue;
            }
            if (!$columnExtension->getManaged()) {
                continue;
            }

            $value = ModelHelper::readPropertyValue($object, $property);
            ObjectType::writeInternalProperty($object, $property->getName(),  $value);
        }
    }
}