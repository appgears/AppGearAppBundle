<?php

namespace AppGear\AppBundle\EntityExtension;

use AppGear\AppBundle\EntityExtension\Sluggable\Transliterator;
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
    public function compute($object, $field, array $options = [])
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver
            ->setRequired('field');

        $options        = $optionsResolver->resolve($options);
        $transliterator = new Transliterator();

        $text = $object->{'get' . ucfirst($options['field'])}();
        $text = $transliterator->transliterate($text);

        return $text;
    }
}