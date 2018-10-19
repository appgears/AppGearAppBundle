<?php

namespace AppGear\AppBundle\View\Introspection;

use AppGear\AppBundle\Entity\View;
use AppGear\CoreBundle\Helper\ModelHelper;
use AppGear\CoreBundle\Model\ModelManager;

class ModelIntrospection implements IntrospectionInterface
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * Constructor
     *
     * @param ModelManager $modelManager
     */
    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    /**
     * {@inheritdoc}
     */
    public function introspect($target)
    {
        if (!$this->modelManager->isModel($target)) {
            return [];
        }

        return array_map(
            function ($property) {
                $viewField = new View\Field();
                $viewField
                    ->setName($property->getName())
                    ->setMapping($property->getName());

                return $viewField;
            },
            iterator_to_array(ModelHelper::getProperties($target))
        );
    }
}