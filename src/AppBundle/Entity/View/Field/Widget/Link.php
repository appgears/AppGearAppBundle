<?php

namespace AppGear\AppBundle\Entity\View\Field\Widget;

use AppGear\AppBundle\Entity\View\Field\Widget;
class Link extends Widget
{
    
    /**
     * Route
     */
    protected $route;
    
    /**
     * Parameters
     */
    protected $parameters = array();
    
    /**
     * Set route
     */
    public function setRoute($route)
    {
        $this->route = $route;
        return $this;
    }
    
    /**
     * Get route
     */
    public function getRoute()
    {
        return $this->route;
    }
    
    /**
     * Set parameters
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
        return $this;
    }
    
    /**
     * Get parameters
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}