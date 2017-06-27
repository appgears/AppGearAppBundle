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
     * Fields
     */
    protected $fields = array();
    
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