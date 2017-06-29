<?php

namespace AppGear\AppBundle\Entity\View\Field\Widget\Link;

class Parameter
{
    
    /**
     * Property
     */
    protected $property;
    
    /**
     * Parameter
     */
    protected $parameter;
    
    /**
     * Set property
     */
    public function setProperty($property)
    {
        $this->property = $property;
        return $this;
    }
    
    /**
     * Get property
     */
    public function getProperty()
    {
        return $this->property;
    }
    
    /**
     * Set parameter
     */
    public function setParameter($parameter)
    {
        $this->parameter = $parameter;
        return $this;
    }
    
    /**
     * Get parameter
     */
    public function getParameter()
    {
        return $this->parameter;
    }
}