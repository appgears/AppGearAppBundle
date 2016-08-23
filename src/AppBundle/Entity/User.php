<?php

namespace AppGear\AppBundle\Entity;

class User
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