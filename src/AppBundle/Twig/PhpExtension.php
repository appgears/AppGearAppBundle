<?php

namespace AppGear\AppBundle\Twig;

use AppGear\PlatformBundle\Service\TaggedManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Расширение делает доступными в шаблонах встроенные функции PHP
 *
 * Доступные в шаблонах функции задаются через конфигурационный файл
 *
 * @package AppGear\PlatformBundle\Twig
 */
class PhpExtension extends \Twig_Extension
{
    /**
     * Список доступных функций php для использования в шаблонах
     *
     * @var array
     */
    private $availableFunctions;

    /**
     * @param array $availableFunctions Список доступных функций php для использования в шаблонах
     */
    public function __construct(array $availableFunctions)
    {
        $this->availableFunctions = $availableFunctions;
    }


    /**
     * * {@inheritdoc}
     */
    public function getFilters()
    {
        $result = array();

        foreach ($this->availableFunctions as $function) {

            if (is_array($function) && !is_numeric(key($function))) {
                $callback = current($function);
                $function = key($function);
            } else {
                $callback = $function;
            }

            $result[] =  new \Twig_SimpleFilter($function, $callback);
        }

        return $result;
    }


    /**
     * {@inheritdoc};
     */
    public function getName()
    {
        return 'appgear_app_php_extension';
    }
}