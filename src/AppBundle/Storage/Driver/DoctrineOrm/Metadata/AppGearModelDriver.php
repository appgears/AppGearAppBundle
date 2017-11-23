<?php

namespace AppGear\AppBundle\Storage\Driver\DoctrineOrm\Metadata;

use AppGear\AppBundle\Entity\Storage\Column;
use AppGear\AppBundle\Entity\Storage\Table;
use AppGear\AppBundle\Storage\Platform\MysqlFieldTypeServiceInterface;
use AppGear\CoreBundle\DependencyInjection\TaggedManager;
use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Entity\Property;
use AppGear\CoreBundle\Entity\Property\Field;
use AppGear\CoreBundle\EntityService\PropertyService;
use AppGear\CoreBundle\Helper\ModelHelper;
use AppGear\CoreBundle\Model\ModelManager;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ORM\Id\IdentityGenerator;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class AppGearModelDriver implements MappingDriver
{
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
     * Supported model prefixes
     *
     * @var array
     */
    private $prefixes = [];

    /**
     * AppGearModelDriver constructor.
     *
     * @param ModelManager  $modelManager  Model manager
     * @param TaggedManager $taggedManager Tagged manager
     * @param array         $prefixes      Supported model prefixes
     */
    public function __construct(ModelManager $modelManager, TaggedManager $taggedManager, array $prefixes)
    {
        $this->modelManager  = $modelManager;
        $this->taggedManager = $taggedManager;
        $this->prefixes      = $prefixes;
    }

    /**
     * {@inheritdoc}
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata)
    {
        /** @var ClassMetadataInfo $metadata */

        $model = $this->modelManager->getByInstance($className);

        /** @var $tableExtension Table */
        if (null !== $tableExtension = ModelHelper::getExtension($model, Table::class)) {
            $metadata->setPrimaryTable(['name' => $tableExtension->getName()]);
        } else {
            $metadata->setPrimaryTable(['name' => $this->buildTableName($className)]);
        }

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

        if ($parents = iterator_to_array(ModelHelper::getParents($model))) {
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
        $classNames       = [];
        $registeredModels = $this->modelManager->all();
        foreach ($registeredModels as $model) {
            foreach ($this->prefixes as $prefix) {
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
        $supported = false;
        foreach ($this->prefixes as $prefix) {
            $prefix = $this->modelManager->fullClassName($prefix);
            if (strpos($className, $prefix) === 0) {
                $supported = true;
            }
        }

        if (!$supported) {
            return true;
        }

        $model = $this->modelManager->getByInstance($className);

        return $model->getAbstract() === false;
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
            $columnExtension = (new PropertyService($property))->getExtension(Column::class);

            if ($property instanceof Field) {
                $mapping = [
                    'fieldName' => $property->getName(),
                    'type'      => $this->resolveFieldType($property),
                    'nullable'  => true,
                    'options'   => []
                ];

                if ($columnExtension !== null && $columnExtension->getIdentifier()) {
                    $mapping['id'] = true;
                    if ($property instanceof Field\Integer) {
                        $mapping['options']['unsigned'] = true;
                    }
                    $classMetadata->setIdGenerator(new IdentityGenerator());
                    $classMetadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
                }

                $classMetadata->mapField($mapping);
            } elseif ($property instanceof Property\Relationship) {
                $targetModel       = $property->getTarget();
                $targetEntityClass = $this->modelManager->fullClassName($targetModel->getName());

                $mapping = [
                    'fieldName'    => $property->getName(),
                    'targetEntity' => $targetEntityClass
                ];

                if ($columnExtension !== null) {
                    if (strlen($mappedBy = $columnExtension->getMappedBy())) {
                        $mapping['mappedBy'] = $mappedBy;
                    } elseif ($inversedBy = $columnExtension->getInversedBy()) {
                        $mapping['inversedBy'] = $inversedBy;
                    }

                    if (strlen($orderBy = $columnExtension->getOrderBy())) {
                        $mapping['orderBy'] = [$orderBy => 'ASC'];
                    }
                }

                if (!isset($mapping['mappedBy'])) {
                    $joinColumn = [
                        'name'                 => $property->getName() . '_id',
                        'referencedColumnName' => 'id'
                    ];

                    $targetProperty = null;
                    if (isset($mapping['inversedBy'])) {
                        $targetProperty = ModelHelper::getProperty($property->getTarget(), $mapping['inversedBy']);
                    }

                    if ($property->getComposition() || ($targetProperty !== null && $targetProperty->getComposition())) {
                        $joinColumn['onDelete'] = 'CASCADE';
                    }

                    $mapping['joinColumns'][] = $joinColumn;
                } else {
                    if ($property->getComposition()) {
                        $mapping['cascade'] = ['persist', 'remove'];
                    }
                }

                if ($property instanceof Property\Relationship\ToOne) {
                    $classMetadata->mapManyToOne($mapping);
                } elseif ($property instanceof Property\Relationship\ToMany) {
                    if ($this->isManyToMany($property, $mapping)) {
                        $classMetadata->mapManyToMany($mapping);
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
        $oppositeProperty     = ModelHelper::getProperty($property->getTarget(), $oppositePropertyName);

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