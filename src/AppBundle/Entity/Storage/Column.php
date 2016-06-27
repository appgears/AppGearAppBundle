<?php

namespace AppGear\AppBundle\Entity\Storage;

class Column
{
    
    /**
     * Identifier
     */
    protected $identifier;
    
    /**
     * MappedBy
     */
    protected $mappedBy;
    
    /**
     * InversedBy
     */
    protected $inversedBy;
    
    /**
     * Get identifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }
    
    /**
     * Set identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }
    
    /**
     * Get mappedBy
     */
    public function getMappedBy()
    {
        return $this->mappedBy;
    }
    
    /**
     * Set mappedBy
     */
    public function setMappedBy($mappedBy)
    {
        $this->mappedBy = $mappedBy;
        return $this;
    }
    
    /**
     * Get inversedBy
     */
    public function getInversedBy()
    {
        return $this->inversedBy;
    }
    
    /**
     * Set inversedBy
     */
    public function setInversedBy($inversedBy)
    {
        $this->inversedBy = $inversedBy;
        return $this;
    }
}