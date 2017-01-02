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

class Loader
{
    /**
     * Constructor.
     */
    public function __construct()
    {
    }
}