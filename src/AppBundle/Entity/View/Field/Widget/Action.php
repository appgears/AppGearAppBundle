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
     * Route
     */
    protected $route;
    
    /**
     * Parameters
     */
    protected $parameters = array();
    
    /**
     * Confirm
     */
    protected $confirm = false;
    
    /**
     * Method
     */
    protected $method;
    
    /**
     * NewWindow
     */
    protected $newWindow = false;
    
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
     * Set method
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }
    
    /**
     * Get method
     */
    public function getMethod()
    {
        return $this->method;
    }
    
    /**
     * Set newWindow
     */
    public function setNewWindow($newWindow)
    {
        $this->newWindow = $newWindow;
        return $this;
    }
    
    /**
     * Get newWindow
     */
    public function getNewWindow()
    {
        return $this->newWindow;
    }
}