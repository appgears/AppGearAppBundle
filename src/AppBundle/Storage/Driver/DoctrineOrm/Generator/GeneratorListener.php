<?php

namespace AppGear\AppBundle\Storage\Driver\DoctrineOrm\Generator;

use AppGear\AppBundle\Helper\StorageHelper;
use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Helper\PropertyHelper;
use AppGear\CoreBundle\Model\Generator\GenerateModelEvent;
use AppGear\CoreBundle\Model\Generator\GeneratePropertyEvent;
use PhpParser\BuilderFactory;

class GeneratorListener
{
    /**
     * Add ID property to the children model class, if parent model is abstract model
     *
     * @param GenerateModelEvent $event Event
     */
    public function addIdentifierProperty(GenerateModelEvent $event)
    {
        $model = $event->getModel();

        /** @var Model $parent */
        if (null !== $parent = $model->getParent()) {
            if (!$parent->getAbstract() && (null !== $property = StorageHelper::getIdentifierProperty($parent))) {

                $class   = $event->getClass();
                $factory = new BuilderFactory();

                $builder = $factory->property($property->getName())->makeProtected();
                $node    = $builder->getNode();
                $class->addStmt($node);
            }
        }
    }

    /**
     * @param GeneratePropertyEvent $event
     */
    public function processCalculatedProperty(GeneratePropertyEvent $event)
    {
        $sourceGenerator = $event->getSourceGenerator();
        $property        = $event->getProperty();

        if (PropertyHelper::isCalculated($property) && StorageHelper::isManagedProperty($property)) {

            // Field
            $sourceGenerator->addField($property);

            // Updater
            $updater = 'update' . ucfirst($property->getName());
            $code    = '<?php $this->' . $property->getName() . ' = $this->get' . ucfirst($property->getName()) . '();';

            $sourceGenerator->addMethod($updater, [], $code, 'Update ' . $property->getName());
        }
    }
}