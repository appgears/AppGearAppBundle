<?php

namespace AppGear\AppBundle\Entity;

class Tree
{
    
    /**
     * ByRelationship
     */
    protected $byRelationship;
    
    /**
     * Set byRelationship
     */
    public function setByRelationship($byRelationship)
    {
        $this->byRelationship = $byRelationship;
        return $this;
    }
    
    /**
     * Get byRelationship
     */
    public function getByRelationship()
    {
        return $this->byRelationship;
    }
}