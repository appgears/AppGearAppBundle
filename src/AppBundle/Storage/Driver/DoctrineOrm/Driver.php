<?php

namespace AppGear\AppBundle\Storage\Driver\DoctrineOrm;

use AppGear\AppBundle\Entity\Storage\Criteria as StorageCriteria;
use AppGear\AppBundle\Storage\DriverInterface;
use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Entity\Property;
use AppGear\CoreBundle\Helper\ModelHelper;
use AppGear\CoreBundle\Model\ModelManager;
use Cosmologist\Gears\ArrayType;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\Criteria as DoctrineCriteria;
use Doctrine\ORM\EntityRepository;
use RuntimeException;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\Node\ArrayNode;
use Symfony\Component\ExpressionLanguage\Node\BinaryNode;
use Symfony\Component\ExpressionLanguage\Node\ConstantNode;
use Symfony\Component\ExpressionLanguage\Node\NameNode;

class Driver implements DriverInterface
{
    /**
     * Object $registry
     *
     * @var Registry
     */
    protected $registry;

    /**
     * Model manager
     *
     * @var ModelManager
     */
    private $modelManager;

    /**
     * Expression language component
     *
     * @var ExpressionLanguage
     */
    private $expressionLanguage;

    /**
     * Constructor
     *
     * @param Registry     $registry     Doctrine registry
     * @param ModelManager $modelManager Model manager
     */
    public function __construct(Registry $registry, ModelManager $modelManager)
    {
        $this->registry           = $registry;
        $this->modelManager       = $modelManager;
        $this->expressionLanguage = new ExpressionLanguage();
    }

    /**
     * {@inheritdoc}
     */
    public function find($model, $id)
    {
        return $this->getObjectRepository($model)->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findAll($model)
    {
        return $this->getObjectRepository($model)->findAll();
    }

    /**
     * {@inheritdoc}
     */
    public function findBy($model, StorageCriteria $criteria = null, array $orderBy = null, $limit = null, $offset = null)
    {
        $doctrineCriteria = DoctrineCriteria::create();
        $this->convertCriteria($this->modelManager->get($model), $doctrineCriteria, $criteria);

        if (null !== $orderBy) {
            $doctrineCriteria->orderBy($orderBy);
        }
        if (null !== $limit) {
            $doctrineCriteria->setMaxResults($limit);
        }
        if (null !== $offset) {
            $doctrineCriteria->setFirstResult($offset);
        }

        return $this->getObjectRepository($model)->matching($doctrineCriteria);
    }

    /**
     * {@inheritdoc}
     */
    public function countBy($model, StorageCriteria $criteria = null)
    {
        $doctrineCriteria = DoctrineCriteria::create();
        $this->convertCriteria($this->modelManager->get($model), $doctrineCriteria, $criteria);

        return $this->getObjectRepository($model)->matching($doctrineCriteria)->count();
    }

    /**
     * @param DoctrineCriteria $doctrineCriteria
     * @param StorageCriteria  $storageCriteria
     * @param bool             $andWhere
     *
     * @return DoctrineCriteria
     */
    private function convertCriteria(Model $model, DoctrineCriteria $doctrineCriteria, StorageCriteria $storageCriteria = null, $andWhere = true)
    {
        if ($storageCriteria === null) {
            return $doctrineCriteria;
        }

        if ($storageCriteria instanceof StorageCriteria\Composite) {
            foreach ($storageCriteria->getExpressions() as $expression) {
                $this->convertCriteria($model, $doctrineCriteria, $expression, strtoupper($storageCriteria->getOperator()) === 'AND');
            }
        } elseif ($storageCriteria instanceof StorageCriteria\Expression) {
            $doctrineExpression = DoctrineCriteria::expr();

            $field      = $storageCriteria->getField();
            $comparison = $storageCriteria->getComparison();
            $value      = $storageCriteria->getValue();

            if (\in_array($comparison, ['eq', 'neq'])) {
                $property = ModelHelper::getProperty($model, $field);

                // Doctrine need "in" expression for relationships
                if (($property instanceof Property\Relationship) && ($value !== null)) {
                    $doctrineExpression = ($comparison === 'eq') ? $doctrineExpression->in($field, [$value]) : $doctrineExpression->notIn($field, [$value]);
                } else {
                    $doctrineExpression = ($comparison === 'eq') ? $doctrineExpression->eq($field, $value) : $doctrineExpression->neq($field, $value);
                }
            } elseif ($comparison === '>') {
                $doctrineExpression = $doctrineExpression->lt($field, $value);
            } elseif ($comparison === '<') {
                $doctrineExpression = $doctrineExpression->gt($field, $value);
            } elseif ($comparison === 'in') {
                $doctrineExpression = $doctrineExpression->in($field, $value);
            } else {
                throw new RuntimeException("Unknown criteria comparison: $comparison");
            }

            if ($andWhere) {
                $doctrineCriteria->andWhere($doctrineExpression);
            } else {
                $doctrineCriteria->orWhere($doctrineExpression);
            }
        }

        return $doctrineCriteria;
    }

    /**
     * {@inheritdoc}
     */
    public function save($object)
    {
        $manager = $this->registry->getManagerForClass(get_class($object));

        $manager->persist($object);
        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove($object)
    {
        $manager = $this->registry->getManagerForClass(get_class($object));

        $manager->remove($object);
        $manager->flush();
    }

    /**
     * Return ObjectRepository for the model
     *
     * @param string $model Model
     *
     * @return EntityRepository
     */
    protected function getObjectRepository($model)
    {
        $fqcn = $this->modelManager->fullClassName($model);

        /** @var EntityRepository $repository */
        $repository = $this->registry->getManagerForClass($fqcn)->getRepository($fqcn);

        return $repository;
    }
}