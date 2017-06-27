<?php

namespace AppGear\AppBundle\Entity\View;

class Field
{
    
    /**
     * Name
     */
    protected $name;
    
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