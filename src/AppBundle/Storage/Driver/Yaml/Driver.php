<?php

namespace AppGear\AppBundle\Storage\Driver\Yaml;

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
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\Node\BinaryNode;
use Symfony\Component\ExpressionLanguage\Node\ConstantNode;
use Symfony\Component\ExpressionLanguage\Node\NameNode;

class Driver extends DriverAbstract
{
    /**
     * Model manager
     *
     * @var ModelManager
     */
    private $modelManager;
    /**
     * @var ConfigCache
     */
    private $configCache;

    /**
     * Constructor.
     *
     * @param ModelManager $modelManager Model manager
     * @param ConfigCache  $configCache  Config cache
     */
    public function __construct(ModelManager $modelManager, ConfigCache $configCache)
    {
        $this->modelManager = $modelManager;
        $this->configCache  = $configCache;
    }

    /**
     * Load data
     *
     * @return array
     */
    protected function loadData()
    {
        if (!$this->configCache->isFresh()) {

        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(Model $model)
    {
        throw new \RuntimeException('Not implemented yet');
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(Model $model, array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        throw new \RuntimeException('Not implemented yet');
    }

    /**
     * {@inheritdoc}
     */
    public function find(Model $model, $id)
    {
        throw new \RuntimeException('Not implemented yet');
    }

    /**
     * {@inheritdoc}
     */
    public function findByExpr(Model $model, $expr, array $orderings = [])
    {
        throw new \RuntimeException('Not implemented yet');
    }

    /**
     * {@inheritdoc}
     */
    public function save(Model $model, $object)
    {
        throw new \RuntimeException('Not implemented yet');
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Model $model, $object)
    {
        throw new \RuntimeException('Not implemented yet');
    }
}