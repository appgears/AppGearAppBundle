<?php

namespace AppGear\AppBundle\Entity;

class View
{
    
    /**
     * Template
     */
    protected $template;
    
    /**
     * Set template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }
    
    /**
     * Get template
     */
    public function getTemplate()
    {
        return $this->template;
    }
}