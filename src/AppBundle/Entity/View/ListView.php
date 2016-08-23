<?php

namespace AppGear\AppBundle\Entity\View;

use AppGear\AppBundle\Entity\View;
abstract class ListView extends View
{
    
    /**
     * Entities
     */
    protected $entities;
    
    /**
     * Model
     */
    protected $model;
    
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
    
    /**
     * Set model
     */
    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }
    
    /**
     * Get model
     */
    public function getModel()
    {
        return $this->model;
    }
}