<?php

namespace AppGear\AppBundle\Storage\Driver\DoctrineOrm;

use AppGear\AppBundle\Storage\DriverAbstract;
use AppGear\CoreBundle\DependencyInjection\TaggedManager;
use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Entity\Property;
use AppGear\CoreBundle\Entity\Property\Field;
use AppGear\CoreBundle\EntityService\ModelService;
use AppGear\CoreBundle\Model\ModelManager;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\Node\BinaryNode;
use Symfony\Component\ExpressionLanguage\Node\ConstantNode;
use Symfony\Component\ExpressionLanguage\Node\NameNode;

class Driver extends DriverAbstract
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
        $names        = array_map(
            function ($property) {
                return $property->getName();
            },
            $properties
        );

        $node = $this->expressionLanguage->parse($expr, $names)->getNodes();

        $criteria = Criteria::create();
        $expr     = Criteria::expr();
        if ($node instanceof BinaryNode) {
            $left     = $node->nodes['left'];
            $right    = $node->nodes['right'];
            $operator = $node->attributes['operator'];

            if ($operator === '==') {
                if ($left instanceof NameNode && $right instanceof ConstantNode) {
                    $name  = $left->attributes['name'];
                    $value = $right->attributes['value'];

                    $property = $modelService->getProperty($name);
                    // Doctrine need "in" expression for relationships
                    if (($property instanceof Property\Relationship) && ($value !== null)) {
                        $expr = $expr->in($name, [$value]);
                    } else {
                        $expr = $expr->eq($name, $value);
                    }
                } else {
                    throw new \RuntimeException(sprintf('Unsupported type of left or right node in expression "%s"', $expr));
                }
            } else {
                throw new \RuntimeException(sprintf('Unsupported operator "%s"', $operator));
            }
        } else {
            throw new \RuntimeException(sprintf('Unsupported note "%s" in expression "%s"', get_class($node), $expr));
        }
        $criteria->andWhere($expr);

        if (count($orderings)) {
            $criteria->orderBy($orderings);
        }

        return $this->getObjectRepository($model)->matching($criteria);
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