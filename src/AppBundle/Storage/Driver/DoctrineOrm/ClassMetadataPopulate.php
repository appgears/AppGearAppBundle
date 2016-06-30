<?php

namespace AppGear\AppBundle\Storage\Driver\DoctrineOrm;

use AppGear\AppBundle\Entity\Storage\Column;
use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\EntityService\ModelService;
use AppGear\CoreBundle\Model\ModelManager;
use AppGear\CoreBundle\Entity\Property;
use AppGear\CoreBundle\Entity\Property\Field;
use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\Common\Persistence\Mapping\RuntimeReflectionService;
use Doctrine\ORM\Id\IdentityGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Provide method for populate MetadataFactory with metadata from the model and related models
 */
class ClassMetadataPopulate
{
    /**
     * Models queue for convert to class metadata
     *
     * @var array
     */
    private $queue = [];

    /**
     * Class metadata factory
     *
     * @var ClassMetadataFactory
     */
    protected $metadataFactory;

    /**
     * Model manager
     *
     * @var ModelManager
     */
    private $modelManager;

    /**
     * ClassMetadataPopulate constructor.
     *
     * @param ClassMetadataFactory $metadataFactory Class metadata factory
     * @param ModelManager         $modelManager    Model manager
     */
    public function __construct(ClassMetadataFactory $metadataFactory, ModelManager $modelManager)
    {
        $this->metadataFactory = $metadataFactory;
        $this->modelManager    = $modelManager;
    }

    /**
     * Populate MetadataFactory with metadata from the model and related models
     *
     * @param Model $model Model
     *
     * @return ClassMetadata
     */
    public function populate(Model $model)
    {
        $classMetadata = null;
        $this->queue[] = $model;

        while ($model = array_shift($this->queue)) {
            $currentClassMetadata = $this->populateClassMetadataFor($model);
            if ($classMetadata === null) {
                $classMetadata = $currentClassMetadata;
            }
        }

        return $classMetadata;
    }

    /**
     * Build class metadata for model and add it to the MetaDataFactory
     *
     * @param Model $model Model
     *
     * @return ClassMetadata
     */
    protected function populateClassMetadataFor(Model $model)
    {
        $entityClass = $this->modelManager->fullClassName($model->getName());

        if ($this->metadataFactory->hasMetadataFor($entityClass)) {
            return $this->metadataFactory->getMetadataFor($entityClass);
        }

        $classMetaData = new ClassMetadata($entityClass);
        $classMetaData->setPrimaryTable(['name' => $this->buildTableName($entityClass)]);

        // Inheritance
        $modelChildren = $this->modelManager->children($model->getName());
        if (count($modelChildren) > 0) {

            $classMetaData->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_JOINED);
            $classMetaData->setDiscriminatorColumn(['name' => '_discriminator']);

            foreach ($modelChildren as $child) {
                $childEntityClass = $this->modelManager->fullClassName($child->getName());
                $this->populateClassMetadataFor($child);
                $classMetaData->addDiscriminatorMapClass(str_replace('\\', '', $childEntityClass), $childEntityClass);
            }
        }

        $this->mapProperties($classMetaData, $model);
        $this->mapInheritedProperties($classMetaData, $model);

        $classMetaData->wakeupReflection(new RuntimeReflectionService);
        $this->metadataFactory->setMetadataFor($entityClass, $classMetaData);

        return $classMetaData;
    }

    /**
     * Map model properties to the class metadata
     *
     * @param ClassMetadata $classMetadata Class metadata
     * @param Model         $model         Model
     */
    protected function mapProperties(ClassMetadata $classMetadata, Model $model)
    {
        foreach ($model->getProperties() as $property) {
            if ($property instanceof Field) {
                $mapping = ['fieldName' => $property->getName(), 'type' => 'string'];
                foreach ($property->getExtensions() as $extension) {
                    if ($extension instanceof Column && $extension->getIdentifier()) {
                        $mapping['id'] = true;
                        $classMetadata->setIdGenerator(new IdentityGenerator());
                        break;
                    }
                }
                $classMetadata->mapField($mapping);
            } elseif ($property instanceof Property\Relationship) {
                $targetModel       = $property->getTarget();
                $targetEntityClass = $this->modelManager->fullClassName($targetModel->getName());

                if ($targetModel->getName() !== $model->getName() || !$this->metadataFactory->hasMetadataFor($targetEntityClass)) {
                    $mapping = [
                        'fieldName' => $property->getName(),
                        'targetEntity' => $targetEntityClass
                    ];
                    foreach ($property->getExtensions() as $extension) {
                        if ($extension instanceof Column) {
                            if (strlen($mappedBy = $extension->getMappedBy())) {
                                $mapping['mappedBy'] = $mappedBy;
                                break;
                            } elseif (strlen($inversedBy = $extension->getInversedBy())) {
                                $mapping['inversedBy'] = $inversedBy;
                                $mapping['joinColumn'] = [
                                    'name' => strtolower($this->modelManager->className($targetModel->getName())) . '_id',
                                    'referencedColumnName' => 'id'
                                ];
                                break;
                            }
                        }
                    }
                    if ($property instanceof Property\Relationship\ToOne) {
                        $classMetadata->mapManyToOne($mapping);
                    } elseif ($property instanceof Property\Relationship\ToMany) {
                        if ($this->isManyToMany($property, $mapping)) {
                            //$classMetadata->mapManyToMany($mapping);
                        } else {
                            $classMetadata->mapOneToMany($mapping);
                        }
                    }

                    $this->queue[] = $property->getTarget();
                }
            }
        }
    }

    /**
     * Map parent models properties to the class metadata as inherited
     *
     * @param ClassMetadata $classMetadata Class metadata
     * @param Model         $model         Model
     */
    protected function mapInheritedProperties(ClassMetadata $classMetadata, Model $model)
    {
        $ms = new ModelService($model);
        foreach ($ms->getParents() as $parent) {
            foreach ($parent->getProperties() as $property) {
                if ($property instanceof Field) {
                    $mapping = [
                        'columnName' => $property->getName(),
                        'fieldName' => $property->getName(),
                        'inherited' => true,
                        'type' => 'string'
                    ];
                    foreach ($property->getExtensions() as $extension) {
                        if ($extension instanceof Column && $extension->getIdentifier()) {
                            $mapping['id'] = true;
                            $classMetadata->setIdGenerator(new IdentityGenerator());
                            $classMetadata->setIdentifier([$property->getName()]);
                            break;
                        }
                    }
                    $classMetadata->addInheritedFieldMapping($mapping);
                } elseif ($property instanceof Property\Relationship) {
                    $targetModel       = $property->getTarget();
                    $targetEntityClass = $this->modelManager->fullClassName($targetModel->getName());

                    $mapping     = [
                        'fieldName' => $property->getName(),
                        'targetEntity' => $targetEntityClass,
                        'sourceEntity' => $this->modelManager->fullClassName($model->getName()),
                        'inherited' => true,
                        'isOwningSide' => true,
                        'fetch' => ClassMetadataInfo::FETCH_LAZY,
                        'inversedBy' => false,
                        'mappedBy' => false
                    ];
                    foreach ($property->getExtensions() as $extension) {
                        if ($extension instanceof Column) {
                            if (strlen($mappedBy = $extension->getMappedBy())) {
                                $mapping['mappedBy']     = $mappedBy;
                                $mapping['isOwningSide'] = false;
                                break;
                            } elseif (strlen($inversedBy = $extension->getInversedBy())) {
                                $mapping['inversedBy'] = $inversedBy;
                                $mapping['joinColumn'] = [
                                    'name' => strtolower($this->modelManager->className($targetModel->getName())) . '_id',
                                    'referencedColumnName' => 'id'
                                ];
                                break;
                            }
                        }
                    }

                    if ($property instanceof Property\Relationship\ToOne) {
                        $mapping['type']                     = ClassMetadataInfo::MANY_TO_ONE;
                        $mapping['targetToSourceKeyColumns'] = [
                            'id' => strtolower($this->modelManager->className($targetModel->getName())) . '_id'
                        ];
                    } elseif ($property instanceof Property\Relationship\ToMany) {
                        if ($this->isManyToMany($property, $mapping)) {
                            $mapping['type'] = ClassMetadataInfo::MANY_TO_MANY;
                        } else {
                            $mapping['type']                     = ClassMetadataInfo::ONE_TO_MANY;
                            $mapping['targetToSourceKeyColumns'] = [
                                'id' => strtolower($this->modelManager->className($targetModel->getName())) . '_id'
                            ];
                        }
                    }
                    $classMetadata->addInheritedAssociationMapping($mapping);
                }
            }
        }
    }


    /**
     * Determine if relationship is ManyToMany
     *
     * @param Property $property Property
     * @param array    $mapping  Mapping info
     *
     * @return bool
     */
    private function isManyToMany(Property $property, array $mapping)
    {
        if (!isset($mapping['inversedBy']) && !isset($mapping['mappedBy'])) {
            return true;
        }

        $oppositePropertyName = (isset($mapping['inversedBy'])) ? $mapping['inversedBy'] : $mapping['mappedBy'];
        $modelService         = new ModelService($property->getTarget());
        $oppositeProperty     = $modelService->getProperty($oppositePropertyName);

        return ($oppositeProperty instanceof Property\Relationship\ToMany);
    }

    /**
     * Build table name
     *
     * @param string $fqcn The model FQCN
     *
     * @return string
     */
    public function buildTableName($fqcn)
    {
        $tableName = str_replace('Bundle\\Entity\\', '\\', $fqcn);
        $tableName = str_replace('\\', '', $tableName);

        return $tableName;
    }
}