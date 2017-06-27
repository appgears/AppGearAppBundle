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