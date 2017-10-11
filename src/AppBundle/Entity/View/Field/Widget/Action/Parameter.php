<?php

namespace AppGear\AppBundle\Entity\View\Field\Widget\Action;

class Parameter
{
    
    /**
     * Parameter
     */
    protected $parameter;
    
    /**
     * Property
     */
    protected $property;
    
    /**
     * Value
     */
    protected $value;
    
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
     * Set value
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
    
    /**
     * Get value
     */
    public function getValue()
    {
        return $this->value;
    }
}