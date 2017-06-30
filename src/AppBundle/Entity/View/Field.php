<?php

namespace AppGear\AppBundle\Entity\View;

class Field
{
    
    /**
     * Name
     */
    protected $name;
    
    /**
     * Mapping
     */
    protected $mapping;
    
    /**
     * Widget
     */
    protected $widget;
    
    /**
     * Set name
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    /**
     * Get name
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Set mapping
     */
    public function setMapping($mapping)
    {
        $this->mapping = $mapping;
        return $this;
    }
    
    /**
     * Get mapping
     */
    public function getMapping()
    {
        return $this->mapping;
    }
    
    /**
     * Set widget
     */
    public function setWidget($widget)
    {
        $this->widget = $widget;
        return $this;
    }
    
    /**
     * Get widget
     */
    public function getWidget()
    {
        return $this->widget;
    }
}