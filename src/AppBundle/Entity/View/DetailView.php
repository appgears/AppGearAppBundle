<?php

namespace AppGear\AppBundle\Entity\View;

use AppGear\AppBundle\Entity\View;
class DetailView extends View
{
    
    /**
     * Entity
     */
    protected $entity;
    
    /**
     * Set entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
        return $this;
    }
    
    /**
     * Get entity
     */
    public function getEntity()
    {
        return $this->entity;
    }
}