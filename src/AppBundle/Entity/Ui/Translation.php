<?php

namespace AppGear\AppBundle\Entity\Ui;

class Translation
{
    
    /**
     * Label
     */
    protected $label;
    
    /**
     * Set label
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }
    
    /**
     * Get label
     */
    public function getLabel()
    {
        return $this->label;
    }
}