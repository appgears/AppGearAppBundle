<?php

namespace AppGear\AppBundle\Form\Transformer;

use Symfony\Component\Form\Extension\Core\DataTransformer\ChoicesToValuesTransformer;
use Traversable;

class ChoicesCollectionToValuesTransformer extends ChoicesToValuesTransformer
{
    /**
     * {@inheritdoc}
     */
    public function transform($array)
    {
        if (null === $array) {
            return array();
        }

        if ($array instanceof Traversable) {
            $array = iterator_to_array($array);
        }

        return parent::transform($array);
    }
}