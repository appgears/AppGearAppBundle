<?php

namespace AppGear\AppBundle\Entity\View\ListView;

class Filter
{
    
    /**
     * Name
     */
    protected $name;
    
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