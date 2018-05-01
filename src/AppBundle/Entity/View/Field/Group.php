<?php

namespace AppGear\AppBundle\Entity\View\Field;

class Group
{
    
    /**
     * Name
     */
    protected $name;
    
    /**
     * Fields
     */
    protected $fields = array();
    
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
     * Set fields
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
        return $this;
    }
    
    /**
     * Get fields
     */
    public function getFields()
    {
        return $this->fields;
    }
}