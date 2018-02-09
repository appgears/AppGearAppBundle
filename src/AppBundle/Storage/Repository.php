<?php

namespace AppGear\AppBundle\Storage;

use AppGear\AppBundle\Entity\Storage\Criteria;
use AppGear\CoreBundle\Helper\ModelHelper;
use AppGear\CoreBundle\Model\ModelManager;
use Cosmologist\Gears\ArrayType;
use RuntimeException;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\Node\ArrayNode;
use Symfony\Component\ExpressionLanguage\Node\BinaryNode;
use Symfony\Component\ExpressionLanguage\Node\ConstantNode;
use Symfony\Component\ExpressionLanguage\Node\NameNode;
use Symfony\Component\ExpressionLanguage\Node\Node;

class Repository
{
    /**
     * Model
     *
     * @var string
     */
    private $model;

    /**
     * Storage driver
     *
     * @var DriverInterface
     */
    private $driver;

    /**
     * Model manager
     *
     * @var ModelManager
     */
    private $modelManager;

    /**
     * CrudController constructor.
     *
     * @param DriverInterface $driver       Storage driver
     * @param string          $model        Model
     * @param ModelManager    $modelManager Model manager
     */
    public function __construct(DriverInterface $driver, string $model, ModelManager $modelManager)
    {
        $this->model        = $model;
        $this->driver       = $driver;
        $this->modelManager = $modelManager;
    }

    /**
     * Finds an object by its primary key/identifier.
     *
     * @param mixed $id The identifier.
     *
     * @return object The object.
     */
    public function find($id)
    {
        return $this->driver->find($this->model, $id);
    }

    /**
     * Finds all objects in the repository.
     *
     * @return array The objects.
     */
    public function findAll()
    {
        return $this->driver->findAll($this->model);
    }

    /**
     * Finds objects by a set of criteria.
     *
     * Optionally sorting and limiting details can be passed.
     *
     * @param Criteria   $criteria
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return array The objects.
     */
    public function findBy(Criteria $criteria = null, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->driver->findBy($this->model, $criteria, $orderBy, $limit, $offset);
    }

    /**
     * Counts objects by a set of criteria.
     *
     * Optionally sorting and limiting details can be passed.
     *
     * @param Criteria $criteria
     *
     * @return int Count
     */
    public function countBy(Criteria $criteria = null)
    {
        return $this->driver->countBy($this->model, $criteria);
    }

    /**
     * Finds a single object by a set of criteria.
     *
     * Optionally sorting and limiting details can be passed.
     *
     * @param Criteria   $criteria
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return array The objects.
     */
    public function findOneBy(Criteria $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $entities = $this->findBy($criteria, $orderBy, $limit, $offset);

        if (count($entities) > 0) {
            return $entities[0];
        }

        return null;
    }

    /**
     * Finds entities by criteria expression.
     *
     * @param string   $expr    Expression language criteria string
     * @param array    $orderBy The orderings
     *                          Keys are field and values are the order, being either ASC or DESC.
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array The objects.
     */
    public function findByExpr($expr, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->findBy($this->convertExpression2Criteria($expr, $this->model), $orderBy, $limit, $offset);
    }

    /**
     * Entities count by criteria expression.
     *
     * @param string $expr Expression language criteria string
     *
     * @return int Count
     */
    public function countByExpr($expr)
    {
        return $this->countBy($this->convertExpression2Criteria($expr, $this->model));
    }

    /**
     * Finds a single object by a criteria expression.
     *
     * @param string $expr      Expression language criteria string
     * @param array  $orderings The orderings
     *                          Keys are field and values are the order, being either ASC or DESC.
     *
     * @return object|null The object or null.
     */
    public function findOneByExpr($expr, array $orderings = [])
    {
        $entities = $this->findByExpr($expr, $orderings);

        if (count($entities) > 0) {
            return $entities[0];
        }

        return null;
    }

    /**
     * Save object to the storage.
     *
     * @param object $object Object
     *
     * @return void
     */
    public function save($object)
    {
        $this->driver->save($object);
    }

    public function remove($object)
    {
        $this->driver->remove($object);
    }

    /**
     * Convert string expression to model to storage criteria
     *
     * @param string $expression
     *
     * @return Criteria
     */
    public function convertExpression2Criteria(string $expression): Criteria
    {
        $model = $this->modelManager->get($this->model);
        $names = ArrayType::collect(ModelHelper::getProperties($model), 'name');

        $expressionLanguage = new ExpressionLanguage();

        $node = $expressionLanguage->parse($expression, $names)->getNodes();

        return $this->convertCriteriaNode($node);
    }

    /**
     * Convert expression node to appgear storage criteria
     *
     * @param Node $node
     *
     * @return Criteria
     */
    private function convertCriteriaNode(Node $node): Criteria
    {
        if (!($node instanceof BinaryNode)) {
            throw new RuntimeException(sprintf('Unsupported node "%s"', get_class($node)));
        }

        $left     = $node->nodes['left'];
        $right    = $node->nodes['right'];
        $operator = $node->attributes['operator'];

        if (in_array($operator, ['==', '!=', '<', '>', 'in'])) {
            if (!($left instanceof NameNode && ($right instanceof ConstantNode || $right instanceof ArrayNode))) {
                throw new RuntimeException(sprintf('Unsupported type of left or right node in expression "%s" or "%s"', get_class($left), get_class($right)));
            }

            if ($right instanceof ConstantNode) {
                $value = $right->attributes['value'];
            } else {
                $value = $this->convertArrayNodeToArray($right);
            }

            switch ($operator) {
                case '==':
                    $operator = 'eq';
                    break;
                case '!=':
                    $operator = 'neq';
                    break;
                case '<':
                    $operator = 'lt';
                    break;
                case '>':
                    $operator = 'gt';
                    break;
                default:
                    break;
            }

            $expression = new Criteria\Expression();
            $expression
                ->setField($left->attributes['name'])
                ->setComparison($operator)
                ->setValue($value);

            /** @var Criteria\Composite $criteria */
            return $expression;

        } elseif (in_array($operator, ['and', '&&'])) {
            $composite = new Criteria\Composite();
            $composite
                ->setOperator('and')
                ->setExpressions([$this->convertCriteriaNode($left), $this->convertCriteriaNode($right)]);

            return $composite;
        } else {
            throw new RuntimeException(sprintf('Unsupported operator "%s"', $operator));
        }
    }

    /**
     * Convert expression ArrayNode to associative array
     *
     * @param ArrayNode $node
     *
     * @return array
     */
    private function convertArrayNodeToArray(ArrayNode $node)
    {
        $keys = $values = [];
        foreach ($node->nodes as $i => $node) {
            if ($i & 1) {
                $values[] = $node->attributes['value'];
            } else {
                $keys[] = $node->attributes['value'];
            }
        }

        return array_combine($keys, $values);
    }
}
