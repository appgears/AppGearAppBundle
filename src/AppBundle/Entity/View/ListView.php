<?php

namespace AppGear\AppBundle\Entity\View;

use AppGear\AppBundle\Entity\View;
class ListView extends View
{
    
    /**
     * Model
     */
    protected $model;
    
    /**
     * Top
     */
    protected $top = array();
    
    /**
     * Fields
     */
    protected $fields = array();
    
    /**
     * ShowCount
     */
    protected $showCount = false;
    
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
    
    /**
     * Set showCount
     */
    public function setShowCount($showCount)
    {
        $this->showCount = $showCount;
        return $this;
    }
    
    /**
     * Get showCount
     */
    public function getShowCount()
    {
        return $this->showCount;
    }
}