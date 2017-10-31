<?php

namespace AppGear\AppBundle\Entity\View\Form;

class Field
{
    
    /**
     * Name
     */
    protected $name;
    
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
}