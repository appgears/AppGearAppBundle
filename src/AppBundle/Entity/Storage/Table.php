<?php

namespace AppGear\AppBundle\Entity\Storage;

use AppGear\CoreBundle\Entity\Extension\Model;
class Table extends Model
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