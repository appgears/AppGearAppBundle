<?php

namespace AppGear\AppBundle\Entity\View\Field\Widget;

use AppGear\AppBundle\Entity\View\Field\Widget;
class Action extends Widget
{
    
    /**
     * Post
     */
    protected $post = false;
    
    /**
     * Confirm
     */
    protected $confirm = false;
    
    /**
     * Route
     */
    protected $route;
    
    /**
     * Parameters
     */
    protected $parameters = array();
    
    /**
     * Set post
     */
    public function setPost($post)
    {
        $this->post = $post;
        return $this;
    }
    
    /**
     * Get post
     */
    public function getPost()
    {
        return $this->post;
    }
    
    /**
     * Set confirm
     */
    public function setConfirm($confirm)
    {
        $this->confirm = $confirm;
        return $this;
    }
    
    /**
     * Get confirm
     */
    public function getConfirm()
    {
        return $this->confirm;
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