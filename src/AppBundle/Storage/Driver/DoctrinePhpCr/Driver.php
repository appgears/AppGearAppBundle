<?php

namespace AppGear\AppBundle\Storage\Driver\DoctrinePhpCr;

use AppGear\AppBundle\Storage\Driver\DoctrinePhpCr\ProxyManager;
use AppGear\AppBundle\Storage\DriverAbstract;
use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Entity\Property;
use AppGear\CoreBundle\Entity\Property\Field;
use AppGear\CoreBundle\Model\Manager;
use AppGear\KbBundle\Entity\Entity\Base\Note;
use Doctrine\Bundle\PHPCRBundle\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\RuntimeReflectionService;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use PHPCR\Util\NodeHelper;
use Symfony\Component\Serializer\Mapping\ClassMetadataInterface;

class Driver extends DriverAbstract
{
    /**
     * Manager registry
     *
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * Proxy manager
     *
     * @var ProxyManager
     */
    private $proxyManager;

    /**
     * DoctrinePhpCr constructor.
     *
     * @param ManagerRegistry $registry     Manager registry
     * @param ProxyManager    $proxyManager Proxy manager
     */
    public function __construct(ManagerRegistry $registry, ProxyManager $proxyManager)
    {
        $this->registry     = $registry;
        $this->proxyManager = $proxyManager;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(Model $model)
    {
        $proxyClass      = $this->proxyManager->getEntityProxyClass($model);
        $manager         = $this->registry->getManager();
        $metadataFactory = $manager->getMetadataFactory();

        if (!$metadataFactory->hasMetadataFor($proxyClass)) {
            $classMetaData = new ClassMetadata($proxyClass);
            $classMetaData->wakeupReflection(new RuntimeReflectionService);
            $classMetaData->mapId(['id' => true, 'fieldName' => 'id', 'strategy' => 'auto']);
            $classMetaData->mapParentDocument(['fieldName' => 'parentDocument']);
            $this->mapProperties($classMetaData, $model->getProperties());
            $metadataFactory->setMetadataFor($proxyClass, $classMetaData);
        }

//        $root = $manager->find(null, '/tasks');
//        if (!$root) {
//            NodeHelper::createPath($this->registry->getConnection(), '/tasks');
//            $root = $manager->find(null, '/tasks');
//        }
//
//        $note = new $proxyClass;
//        $note->setName('123');
//        $note->setDescription('test');
//        $note->setUrl('test1');
//        $note->setParentDocument($root);
//        $manager->persist($note);
//        $manager->flush();
//
//        return [];

        $repository = $manager->getRepository($proxyClass);

        return $repository->findAll();
    }

    /**
     * Map model properties to the class metadata
     *
     * @param ClassMetadata $classMetadata Class metadata
     * @param Property[]    $properties    Properties
     *
     * @throws \Doctrine\ODM\PHPCR\Mapping\MappingException
     */
    protected function mapProperties(ClassMetadata $classMetadata, array $properties)
    {
        foreach ($properties as $property) {
            if ($property instanceof Field) {
                $classMetadata->mapField(['fieldName' => $property->getName(), 'type' => 'string']);
            } elseif ($property instanceof Property\Relationship\ToOne) {
                $classMetadata->mapManyToOne(['fieldName' => $property->getName()]);
            } elseif ($property instanceof Property\Relationship\ToMany) {
                $classMetadata->mapManyToMany(['fieldName' => $property->getName()]);
            }
        }
    }
}
