<?php

namespace AppGear\AppBundle\Storage\Driver\DoctrineOrm;

use AppGear\AppBundle\Storage\DriverAbstract;
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
     * ClassMetadataPopulate instance
     *
     * @var ClassMetadataPopulate
     */
    private $classMetadataPopulate;

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
     */
    public function __construct(ObjectManager $objectManager, ModelManager $modelManager)
    {
        $this->objectManager         = $objectManager;
        $this->modelManager          = $modelManager;
        $this->classMetadataPopulate = new ClassMetadataPopulate($objectManager->getMetadataFactory(), $modelManager);
        $this->expressionLanguage    = new ExpressionLanguage();
    }

    /**
     * {@inheritdoc}
     */
    public function save(Model $model, $object)
    {
        $this->classMetadataPopulate->populate($model);

        $this->objectManager->persist($object);
        $this->objectManager->flush();
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
    public function find(Model $model, $id)
    {
        return $this->getObjectRepository($model)->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findByExpr(Model $model, $expr)
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

        return $this->getObjectRepository($model)->matching($criteria);
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
        $classMetadata = $this->classMetadataPopulate->populate($model);

        return $this->objectManager->getRepository($classMetadata->getName());
    }
}