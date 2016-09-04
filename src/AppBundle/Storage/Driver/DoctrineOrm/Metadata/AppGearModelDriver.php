<?php

namespace AppGear\AppBundle\Storage\Driver\DoctrineOrm\Metadata;

use AppGear\AppBundle\Entity\Storage\Column;
use AppGear\AppBundle\Storage\DriverManager;
use AppGear\AppBundle\Storage\Platform\MysqlFieldTypeServiceInterface;
use AppGear\CoreBundle\DependencyInjection\TaggedManager;
use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Entity\Property;
use AppGear\CoreBundle\Entity\Property\Field;
use AppGear\CoreBundle\EntityService\ModelService;
use AppGear\CoreBundle\Model\ModelManager;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ORM\Id\IdentityGenerator;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class AppGearModelDriver implements MappingDriver
{
    /**
     * DoctrineOrm driver name
     */
    const DRIVER_NAME = 'appgear.storage.driver.doctrine_orm';

    /**
     * Driver manager
     *
     * @var DriverManager
     */
    private $driverManager;

    /**
     * Model manager
     *
     * @var ModelManager
     */
    private $modelManager;

    /**
     * Tagged manager
     *
     * @var TaggedManager
     */
    private $taggedManager;

    /**
     * @param DriverManager $driverManager Driver manager
     *
     * @return AppGearModelDriver
     */
    public function setDriverManager($driverManager)
    {
        $this->driverManager = $driverManager;

        return $this;
    }

    /**
     * @param ModelManager $modelManager
     *
     * @return AppGearModelDriver
     */
    public function setModelManager($modelManager)
    {
        $this->modelManager = $modelManager;

        return $this;
    }

    /**
     * @param TaggedManager $taggedManager
     *
     * @return AppGearModelDriver
     */
    public function setTaggedManager($taggedManager)
    {
        $this->taggedManager = $taggedManager;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata)
    {
        /** @var ClassMetadataInfo $metadata */

        $model = $this->modelManager->getByInstance($className);

        $metadata->setPrimaryTable(['name' => $this->buildTableName($className)]);

        // Если текущая сущность не абстрактная, то надо добавить её в discriminator map
        if (!$model->getAbstract() && count($this->modelManager->children($model->getName())) > 0) {
            $metadata->addDiscriminatorMapClass(str_replace('\\', '', $className), $className);
        }

        // Inheritance
        $modelChildren = $this->modelManager->children($model->getName());
        if (count($modelChildren) > 0) {

            $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_JOINED);
            $metadata->setDiscriminatorColumn(['name' => '_discriminator', 'length' => null]);

            foreach ($modelChildren as $child) {
                $childEntityClass = $this->modelManager->fullClassName($child->getName());
                $metadata->addDiscriminatorMapClass(str_replace('\\', '', $childEntityClass), $childEntityClass);
            }
        }

        $this->mapProperties($metadata, $model);

        if ($parents = (new ModelService($model))->getParents()) {
            $parents = array_map(function ($parent) {
                return $this->modelManager->fullClassName($parent->getName());
            }, $parents);
            $metadata->setParentClasses($parents);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAllClassNames()
    {
        $classNames         = [];
        $registeredPrefixes = $this->driverManager->getPrefixes(self::DRIVER_NAME);
        $registeredModels   = $this->modelManager->all();
        foreach ($registeredModels as $model) {
            foreach ($registeredPrefixes as $prefix) {
                if (strpos($model->getName(), $prefix) === 0) {
                    $classNames[] = $this->modelManager->fullClassName($model->getName());
                }
            }
        }

        return $classNames;
    }

    /**
     * {@inheritdoc}
     */
    public function isTransient($className)
    {
        /** @var ClassMetadataInfo $metadata */

        $model = $this->modelManager->getByInstance($className);

        return !$model->getAbstract();
    }

    /**
     * Map model properties to the class metadata
     *
     * @param ClassMetadata $classMetadata Class metadata
     * @param Model         $model         Model
     */
    protected function mapProperties(ClassMetadata $classMetadata, Model $model)
    {
        /** @var ClassMetadataInfo $classMetadata */

        foreach ($model->getProperties() as $property) {
            if ($property instanceof Field) {
                $mapping = [
                    'fieldName' => $property->getName(),
                    'type' => $this->resolveFieldType($property),
                    'nullable' => true,
                    'options' => []
                ];
                foreach ($property->getExtensions() as $extension) {
                    if ($extension instanceof Column && $extension->getIdentifier()) {
                        $mapping['id'] = true;
                        if ($property instanceof Field\Integer) {
                            $mapping['options']['unsigned'] = true;
                        }
                        $classMetadata->setIdGenerator(new IdentityGenerator());
                        $classMetadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
                        break;
                    }
                }
                $classMetadata->mapField($mapping);
            } elseif ($property instanceof Property\Relationship) {
                $targetModel       = $property->getTarget();
                $targetEntityClass = $this->modelManager->fullClassName($targetModel->getName());

                $mapping = [
                    'fieldName' => $property->getName(),
                    'targetEntity' => $targetEntityClass
                ];
                foreach ($property->getExtensions() as $extension) {
                    if ($extension instanceof Column) {
                        if (strlen($mappedBy = $extension->getMappedBy())) {
                            $mapping['mappedBy'] = $mappedBy;
                        } elseif (strlen($inversedBy = $extension->getInversedBy())) {
                            $mapping['inversedBy'] = $inversedBy;
                            $mapping['joinColumn'] = [
                                'name' => strtolower($this->modelManager->className($targetModel->getName())) . '_id',
                                'referencedColumnName' => 'id'
                            ];
                        }
                        if (strlen($orderBy = $extension->getOrderBy())) {
                            $mapping['orderBy'] = [$orderBy => 'ASC'];
                        }
                    }
                }
                if ($property instanceof Property\Relationship\ToOne) {
                    $classMetadata->mapManyToOne($mapping);
                } elseif ($property instanceof Property\Relationship\ToMany) {
                    if ($this->isManyToMany($property, $mapping)) {
//                        //$classMetadata->mapManyToMany($mapping);
                    } else {
                        $classMetadata->mapOneToMany($mapping);
                    }
                }
            }
        }
    }

    /**
     * Resolve field type for model field
     *
     * @param Field $field Model field
     *
     * @return mixed
     */
    private function resolveFieldType(Field $field)
    {
        $fieldModel = $this->modelManager->getByInstance($field);

        /** @var MysqlFieldTypeServiceInterface $service */
        if ($service = $this->taggedManager->get('storage.platform.mysql.property.field.service', ['field' => $fieldModel->getName()])) {
            return $service->getMysqlFieldType();
        }

        return 'string';
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