<?php

namespace AppGear\AppBundle\Entity\View\Field\Widget;

use AppGear\AppBundle\Entity\View\Field\Widget;
class Action extends Widget
{
    
    /**
     * Type
     */
    protected $type = 'link';
    
    /**
     * Prompt
     */
    protected $prompt = false;
    
    /**
     * Route
     */
    protected $route;
    
    /**
     * Parameters
     */
    protected $parameters = array();
    
    /**
     * Set type
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
    
    /**
     * Get type
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * Set prompt
     */
    public function setPrompt($prompt)
    {
        $this->prompt = $prompt;
        return $this;
    }
    
    /**
     * Get prompt
     */
    public function getPrompt()
    {
        return $this->prompt;
    }
    
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