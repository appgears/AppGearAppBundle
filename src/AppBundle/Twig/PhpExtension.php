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

        // Перебираем все атомы с тегом ag.twig, то есть атомы доступные в твиг-шаблонах
        foreach ($this->availableFunctions as $function) {

            // Создаем замыкание, которое будет вызывать атом
            $callback = function() use($function) {
                $args = func_get_args();
                return call_user_func_array($function, $args);
            };

            // Добавляем всё это в твиг
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