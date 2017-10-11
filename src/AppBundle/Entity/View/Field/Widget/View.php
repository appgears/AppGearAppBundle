<?php

namespace AppGear\AppBundle\Entity\View\Field\Widget;

use AppGear\AppBundle\Entity\View\Field\Widget;
class View extends Widget
{
    
    /**
     * View
     */
    protected $view;
    
    /**
     * Set view
     */
    public function setView($view)
    {
        $this->view = $view;
        return $this;
    }
    
    /**
     * Get view
     */
    public function getView()
    {
        return $this->view;
    }
}