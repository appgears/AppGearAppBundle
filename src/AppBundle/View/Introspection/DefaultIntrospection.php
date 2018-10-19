<?php

namespace AppGear\AppBundle\View\Introspection;

use AppGear\AppBundle\Entity\View;
use AppGear\CoreBundle\Helper\ModelHelper;
use AppGear\CoreBundle\Model\ModelManager;

class DefaultIntrospection implements IntrospectionInterface
{
    /**
     * {@inheritdoc}
     */
    public function introspect($target)
    {
        if (!is_object($target)) {
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