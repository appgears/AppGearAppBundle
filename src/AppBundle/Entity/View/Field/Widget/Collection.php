<?php

namespace AppGear\AppBundle\Entity\View\Field\Widget;

use AppGear\AppBundle\Entity\View\Field\Widget;
class Collection extends Widget
{
    
    /**
     * Widget
     */
    protected $widget;
    
    /**
     * Collapse
     */
    protected $collapse = false;
    
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
    
    /**
     * Set collapse
     */
    public function setCollapse($collapse)
    {
        $this->collapse = $collapse;
        return $this;
    }
    
    /**
     * Get collapse
     */
    public function getCollapse()
    {
        return $this->collapse;
    }
}