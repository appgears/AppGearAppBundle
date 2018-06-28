<?php

namespace AppGear\AppBundle\Storage\Driver\DoctrineOrm\Calculated;

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
     * @var CalculatedFieldManager
     */
    private $manager;

    /**
     * CalculatedFieldSubscriber constructor.
     *
     * @param CalculatedFieldManager $manager
     */
    public function __construct(CalculatedFieldManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'prePersist',
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->manager->update($args->getObject());
    }
}