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
     * Top
     */
    protected $top = array();
    
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
     * Set top
     */
    public function setTop($top)
    {
        $this->top = $top;
        return $this;
    }
    
    /**
     * Get top
     */
    public function getTop()
    {
        return $this->top;
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