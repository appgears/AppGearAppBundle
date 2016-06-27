<?php

namespace AppGear\AppBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class AppGearLoader extends Loader
{
    const LOADER_ALIAS = 'appgear';

    /**
     * Route collection
     *
     * @var RouteCollection
     */
    private $routes;

    /**
     * Loader was loaded
     *
     * @var bool
     */
    private $loaded = false;

    /**
     * AppGearLoader constructor.
     */
    public function __construct()
    {
        $this->routes = new RouteCollection();
    }

    /**
     * Add route
     *
     * @param string $alias        Route alias
     * @param string $controller   Controller specification
     * @param string $pattern      Route pattern
     * @param array  $defaults     Route defaults section
     * @param array  $requirements Route requirements section
     *
     * @return $this
     */
    public function addRoute($alias, $controller, $pattern, $defaults, $requirements)
    {
        $defaults['_controller'] = $controller;

        $route = new Route($pattern, $defaults, $requirements);
        $this->routes->add($alias, $route);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException(sprintf('Do not add the "%s" loader twice', self::LOADER_ALIAS));
        }

        $this->loaded = true;

        return $this->routes;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return self::LOADER_ALIAS === $type;
    }
}