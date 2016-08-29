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
     * DoctrinePhpCr constructor.
     *
     * @param ObjectManager $objectManager Manager registry
     * @param ModelManager  $modelManager  Model manager
     * @param TaggedManager $taggedManager Tagged manager
     */
    public function __construct(ObjectManager $objectManager, ModelManager $modelManager, TaggedManager $taggedManager)
    {
        $this->objectManager      = $objectManager;
        $this->modelManager       = $modelManager;
        $this->expressionLanguage = new ExpressionLanguage();
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(Model $model)
    {
        return $this->getObjectRepository($model)->findAll();
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(Model $model, array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->getObjectRepository($model)->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * {@inheritdoc}
     */
    public function find(Model $model, $id)
    {
        return $this->getObjectRepository($model)->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findByExpr(Model $model, $expr, array $orderings = [])
    {
        $modelService = new ModelService($model);
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
                    if ($property instanceof Property\Relationship) {
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
    public function save(Model $model, $object)
    {
        $this->objectManager->persist($object);
        $this->objectManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Model $model, $object)
    {
        $this->objectManager->remove($object);
        $this->objectManager->flush();
    }

    /**
     * Return ObjectRepository for the model
     *
     * @param Model $model Model
     *
     * @return ObjectRepository
     */
    protected function getObjectRepository(Model $model)
    {
        $fqcn = $this->modelManager->fullClassName($model->getName());

        return $this->objectManager->getRepository($fqcn);
    }
}