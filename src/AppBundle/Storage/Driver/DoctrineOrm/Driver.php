<?php

namespace AppGear\AppBundle\Storage\Driver\DoctrineOrm;

use AppGear\AppBundle\Storage\DriverInterface;
use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Entity\Property;
use AppGear\CoreBundle\EntityService\ModelService;
use AppGear\CoreBundle\Helper\ModelHelper;
use AppGear\CoreBundle\Model\ModelManager;
use Cosmologist\Gears\ArrayType;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
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
    public function findAll($model)
    {
        return $this->getObjectRepository($model)->findAll();
    }

    /**
     * {@inheritdoc}
     */
    public function findBy($model, array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->getObjectRepository($model)->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * {@inheritdoc}
     */
    public function countBy($model, array $criteria)
    {
        $fqcn    = $this->modelManager->fullClassName($model);
        $manager = $this->registry->getManagerForClass($fqcn);

        return $manager->getUnitOfWork()->getEntityPersister($fqcn)->count($criteria);
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
    public function findByExpr($model, $expression, array $orderBy = null, $limit = null, $offset = null)
    {
        $model = $this->modelManager->get($model);
        $names = ArrayType::collect(ModelHelper::getProperties($model), 'name');

        $node     = $this->expressionLanguage->parse($expression, $names)->getNodes();
        $criteria = $this->buildCriteria($model, Criteria::create(), $node);

        if (null !== $orderBy) {
            $criteria->orderBy($orderBy);
        }
        if (null !== $limit) {
            $criteria->setMaxResults($limit);
        }
        if (null !== $offset) {
            $criteria->setMaxResults($offset);
        }

        return $this->getObjectRepository($model)->matching($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function countByExpr($model, $expression)
    {
        $model = $this->modelManager->get($model);
        $names = ArrayType::collect(ModelHelper::getProperties($model), 'name');

        $node     = $this->expressionLanguage->parse($expression, $names)->getNodes();
        $criteria = $this->buildCriteria($model, Criteria::create(), $node);

        return $this->getObjectRepository($model)->matching($criteria)->count();
    }

    /**
     * @param Model    $model
     * @param Criteria $criteria
     * @param          $node
     * @param bool     $andWhere
     *
     * @return Criteria
     */
    private function buildCriteria(Model $model, Criteria $criteria, $node, $andWhere = true)
    {
        if ($node instanceof BinaryNode) {
            $left     = $node->nodes['left'];
            $right    = $node->nodes['right'];
            $operator = $node->attributes['operator'];

            if (\in_array($operator, ['==', '!=', '<', '>', 'in'])) {
                if ($left instanceof NameNode && ($right instanceof ConstantNode || $right instanceof ArrayNode)) {
                    $name = $left->attributes['name'];
                    if ($right instanceof ConstantNode) {
                        $value = $right->attributes['value'];
                    } else {
                        // Extract array from ArrayNode
                        $keys = $values = [];
                        foreach ($right->nodes as $i => $node) {
                            if ($i & 1) {
                                $values[] = $node->attributes['value'];
                            } else {
                                $keys[] = $node->attributes['value'];
                            }
                        }
                        $value = \array_combine($keys, $values);
                    }

                    $property = ModelHelper::getProperty($model, $name);

                    $expr = Criteria::expr();

                    if (\in_array($operator, ['==', '!='])) {
                        // Doctrine need "in" expression for relationships
                        if (($property instanceof Property\Relationship) && ($value !== null)) {
                            $expr = ($operator === '==') ? $expr->in($name, [$value]) : $expr->notIn($name, [$value]);
                        } else {
                            $expr = ($operator === '==') ? $expr->eq($name, $value) : $expr->neq($name, $value);
                        }
                    } elseif ($operator === '>') {
                        $expr = $expr->lt($name, $value);
                    } elseif ($operator === '<') {
                        $expr = $expr->gt($name, $value);
                    } elseif ($operator === 'in') {
                        $expr = $expr->in($name, $value);
                    }
                } else {
                    throw new \RuntimeException(sprintf('Unsupported type of left or right node in expression "%s"', $expr));
                }

                if ($andWhere) {
                    $criteria->andWhere($expr);
                } else {
                    $criteria->orWhere($expr);
                }
            } elseif (\in_array($operator, ['and', '&&'])) {
                $this->buildCriteria($model, $criteria, $left);
                $this->buildCriteria($model, $criteria, $right);
            } elseif (\in_array($operator, ['or', '||'])) {
                $this->buildCriteria($model, $criteria, $left, false);
                $this->buildCriteria($model, $criteria, $right, false);
            } else {
                throw new \RuntimeException(sprintf('Unsupported operator "%s"', $operator));
            }
        } else {
            throw new \RuntimeException(sprintf('Unsupported note "%s" in expression "%s"', get_class($node), $expr));
        }

        return $criteria;
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