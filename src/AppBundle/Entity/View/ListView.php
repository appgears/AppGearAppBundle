<?php

namespace AppGear\AppBundle\Entity\View;

use AppGear\AppBundle\Entity\View;
class ListView extends View
{
    
    /**
     * Entities
     */
    protected $entities;
    
    /**
     * Set entities
     */
    public function setEntities($entities)
    {
        $this->entities = $entities;
        return $this;
    }
    
    /**
     * Get entities
     */
    public function getEntities()
    {
        return $this->entities;
    }
}