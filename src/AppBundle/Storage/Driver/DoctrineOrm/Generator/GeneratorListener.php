<?php

namespace AppGear\AppBundle\Storage\Driver\DoctrineOrm\Generator;

use AppGear\AppBundle\Entity\Storage\Column;
use AppGear\AppBundle\Helper\StorageHelper;
use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Entity\Property;
use AppGear\CoreBundle\EntityService\PropertyService;
use AppGear\CoreBundle\Model\Generator\GeneratorEvent;
use PhpParser\BuilderFactory;

class GeneratorListener
{
    /**
     * Add ID property to the children model class, if parent model is abstract model
     *
     * @param GeneratorEvent $generatorEvent Event
     */
    public function addIdentifierProperty(GeneratorEvent $generatorEvent)
    {
        $model = $generatorEvent->getModel();

        /** @var Model $parent */
        if (null !== $parent = $model->getParent()) {
            if (!$parent->getAbstract() && (null !== $property = StorageHelper::getIdentifierProperty($parent))) {

                $class   = $generatorEvent->getClass();
                $factory = new BuilderFactory();

                $builder = $factory->property($property->getName())->makeProtected();
                $node    = $builder->getNode();
                $class->addStmt($node);
            }
        }
    }
}