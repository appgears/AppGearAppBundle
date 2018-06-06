<?php

namespace AppGear\AppBundle\Entity\Storage;

use AppGear\CoreBundle\Entity\Extension\Property;
class Column extends Property
{
    
    /**
     * Managed
     */
    protected $managed;
    
    /**
     * Name
     */
    protected $name;
    
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
     * OrderBy
     */
    protected $orderBy;
    
    /**
     * Set managed
     */
    public function setManaged($managed)
    {
        $this->managed = $managed;
        return $this;
    }
    
    /**
     * Get managed
     */
    public function getManaged()
    {
        return $this->managed;
    }
    
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
    
    /**
     * Set identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }
    
    /**
     * Get identifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
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
     * Get mappedBy
     */
    public function getMappedBy()
    {
        return $this->mappedBy;
    }
    
    /**
     * Set inversedBy
     */
    public function setInversedBy($inversedBy)
    {
        $this->inversedBy = $inversedBy;
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
     * Set orderBy
     */
    public function setOrderBy($orderBy)
    {
        $this->orderBy = $orderBy;
        return $this;
    }
    
    /**
     * Get orderBy
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }
}