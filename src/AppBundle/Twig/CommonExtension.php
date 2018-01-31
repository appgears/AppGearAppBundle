<?php

namespace AppGear\AppBundle\Twig;

use AppGear\PlatformBundle\Entity\Model\Property\Relationship;
use Exception;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Twig_Extension;
use Twig_SimpleFilter;

/**
 * Common twig extension
 */
class CommonExtension extends Twig_Extension
{
    /**
     * * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new Twig_SimpleFilter('class', array($this, 'getShortClassName')),
            new Twig_SimpleFilter('auto_convert_urls', array($this, 'autoConvertUrls')),
            new Twig_SimpleFilter('expression', array($this, 'expression')),
            new Twig_SimpleFilter('property_accessor', array($this, 'propertyAccessor')),
            new Twig_SimpleFilter('phone_format', array($this, 'formatPhone')),
            new Twig_SimpleFilter('phone_valid', array($this, 'isValidPhone')),
        );
    }

    /**
     * Return short class name
     *
     * @param object|string $input Object or class name
     *
     * @return string
     */
    public function getShortClassName($input)
    {
        return (new \ReflectionClass($input))->getShortName();
    }

    /**
     * Method that finds different occurrences of urls or email addresses in a string.
     *
     * @see https://github.com/liip/LiipUrlAutoConverterBundle/blob/master/Extension/UrlAutoConverterTwigExtension.php
     *
     * @param string $string input string
     *
     * @return string with replaced links
     */
    public function autoConvertUrls($string)
    {
        $pattern        = '/(href="|src=")?([-a-zA-Zа-яёА-ЯЁ0-9@:%_\+.~#?&\/\/=]{2,256}\.[a-zа-яё]{2,4}\b(\/?[-\p{L}0-9@:%_\+.~#?&\/\/=\(\),]*)?)/u';
        $stringFiltered = preg_replace_callback($pattern, array($this, 'callbackReplace'), $string);

        return $stringFiltered;
    }

    /**
     * Evaluate expression language expression with entity as context
     *
     * @param string $expression Expression language expression
     * @param object $entity     Entity
     * @param string $value      Entity value
     *
     * @return string
     */
    public function expression($expression, $entity, $value = null)
    {
        $language = new ExpressionLanguage();

        return $language->evaluate($expression, ['entity' => $entity, 'value' => $value]);
    }

    /**
     * Returns the value at the end of the property path of the object graph.
     *
     * @param object|array $object       The object or array to traverse
     * @param string       $propertyPath The property path to read
     *
     * @return string
     */
    public function propertyAccessor($object, $propertyPath)
    {
        $accessor = new PropertyAccessor();

        return $accessor->getValue($object, $propertyPath);
    }

    /**
     * Is phone number valid
     *
     * @param string $phone Phone number
     *
     * @return bool
     */
    public function isValidPhone(string $phone)
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        try {
            $phoneUtil->parse($phone, 'RU');

            return true;
        } catch (Exception $e) {
        }

        return false;
    }

    /**
     * Format phone string
     *
     * @param string $phone Phone number
     *
     * @return string
     */
    public function formatPhone(string $phone)
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        try {
            $phoneNumber = $phoneUtil->parse($phone, 'RU');
            $phone       = $phoneUtil->format($phoneNumber, PhoneNumberFormat::E164);

            return $phone;
        } catch (Exception $e) {
        }

        return $phone;
    }

    /**
     * {@inheritdoc};
     */
    public function getName()
    {
        return 'appgear_app_common_extension';
    }
}