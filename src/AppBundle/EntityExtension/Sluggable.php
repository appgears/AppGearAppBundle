<?php

namespace AppGear\AppBundle\EntityExtension;

use AppGear\CoreBundle\EntityExtension\ComputedPropertyExtensionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class Sluggable
 */
class Sluggable implements ComputedPropertyExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute($object, $field, array $options = [])
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver
            ->setRequired('field');

        $options        = $optionsResolver->resolve($options);
        $transliterator = new Transliterator();

        $text = $object->{'get' . ucfirst($options['field'])}();
        $text = $transliterator->urlize($text);
        $object->{'set' . ucfirst($field)}($text);
    }
}