<?php

namespace AppGear\AppBundle\Storage\Driver\DoctrineOrm;

use AppGear\AppBundle\Storage\DriverInterface;
use AppGear\CoreBundle\Entity\Property;
use AppGear\CoreBundle\Entity\Property\Field;
use AppGear\CoreBundle\EntityService\ModelService;
use AppGear\CoreBundle\Model\ModelManager;
use Cosmologist\Gears\ArrayType;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\Node\ArrayNode;
use Symfony\Component\ExpressionLanguage\Node\BinaryNode;
use Symfony\Component\ExpressionLanguage\Node\ConstantNode;
use Symfony\Component\ExpressionLanguage\Node\NameNode;

class Driver implements DriverInterface
{
    /**
     * Object manager
     *
     * @var ObjectManager
     */
    protected $objectManager;

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
     * @param ObjectManager $objectManager Manager registry
     * @param ModelManager  $modelManager  Model manager
     */
    public function __construct(ObjectManager $objectManager, ModelManager $modelManager)
    {
        $this->objectManager      = $objectManager;
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
    public function find($model, $id)
    {
        return $this->getObjectRepository($model)->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findByExpr($model, $expr, array $orderings = [])
    {
        $modelService = new ModelService($this->modelManager->get($model));
        $properties   = $modelService->getAllProperties();
        $names        = ArrayType::collect($properties, 'name');

        $node     = $this->expressionLanguage->parse($expr, $names)->getNodes();
        $criteria = $this->buildCriteria(Criteria::create(), $node, $modelService);

        if (count($orderings)) {
            $criteria->orderBy($orderings);
        }

        return $this->getObjectRepository($model)->matching($criteria);
    }

    /**
     * @param $node
     * @param $modelService
     *
     * @return Criteria
     */
    private function buildCriteria($criteria, $node, $modelService, $andWhere = true)
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

                    $property = $modelService->getProperty($name);

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
                $this->buildCriteria($criteria, $left, $modelService);
                $this->buildCriteria($criteria, $right, $modelService);
            } elseif (\in_array($operator, ['or', '||'])) {
                $this->buildCriteria($criteria, $left, $modelService, false);
                $this->buildCriteria($criteria, $right, $modelService, false);
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
        $this->objectManager->persist($object);
        $this->objectManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove($object)
    {
        $this->objectManager->remove($object);
        $this->objectManager->flush();
    }

    /**
     * Return ObjectRepository for the model
     *
     * @param string $model Model
     *
     * @return ObjectRepository
     */
    protected function getObjectRepository($model)
    {
        $fqcn = $this->modelManager->fullClassName($model);

        return $this->objectManager->getRepository($fqcn);
    }
}