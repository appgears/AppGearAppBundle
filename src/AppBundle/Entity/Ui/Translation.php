<?php

namespace AppGear\AppBundle\Entity\Ui;

class Translation
{
    
    /**
     * Label
     */
    protected $label;
    
    /**
     * Get label
     */
    public function getLabel()
    {
        return $this->label;
    }
    
    /**
     * Set label
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }
}