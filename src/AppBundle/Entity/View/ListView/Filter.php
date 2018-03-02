<?php

namespace AppGear\AppBundle\Entity\View\ListView;

class Filter
{
    
    /**
     * Name
     */
    protected $name;
    
    /**
     * Mapping
     */
    protected $mapping;
    
    /**
     * Criteria
     */
    protected $criteria;
    
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
     * Set mapping
     */
    public function setMapping($mapping)
    {
        $this->mapping = $mapping;
        return $this;
    }
    
    /**
     * Get mapping
     */
    public function getMapping()
    {
        return $this->mapping;
    }
    
    /**
     * Set criteria
     */
    public function setCriteria($criteria)
    {
        $this->criteria = $criteria;
        return $this;
    }
    
    /**
     * Get criteria
     */
    public function getCriteria()
    {
        return $this->criteria;
    }
}