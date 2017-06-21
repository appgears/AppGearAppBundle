<?php

namespace AppGear\AppBundle\Twig;

use AppGear\AppBundle\Entity\Ui\Translation;
use AppGear\AppBundle\Entity\View;
use AppGear\CoreBundle\Entity\Property;
use Twig_Extension;
use Twig_SimpleFilter;

/**
 * Twig extension for translation
 */
class TranslationExtension extends Twig_Extension
{
    /**
     * * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new Twig_SimpleFilter('translate', array($this, 'translate')),
        );
    }

    /**
     * Render the view
     *
     * @param Property $property Property
     *
     * @return string
     */
    public function translate(Property $property)
    {
        $extension = (new PropertyService($relationship))->getExtension(Translation::class);
        if ($extension !== null) {
            return $extension->getLabel();
        }

        return $property->getName();
    }

    /**
     * {@inheritdoc};
     */
    public function getName()
    {
        return 'appgear_app_translation_extension';
    }
}