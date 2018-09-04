<?php

namespace AppGear\AppBundle\Entity\View;

use AppGear\AppBundle\Entity\View;
class ListView extends View
{
    
    /**
     * Title
     */
    protected $title;
    
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
     * Filters
     */
    protected $filters = array();
    
    /**
     * ShowCount
     */
    protected $showCount = false;
    
    /**
     * ShowCreateButton
     */
    protected $showCreateButton = false;
    
    /**
     * Set title
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }
    
    /**
     * Get title
     */
    public function getTitle()
    {
        return $this->title;
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
     * Set filters
     */
    public function setFilters($filters)
    {
        $this->filters = $filters;
        return $this;
    }
    
    /**
     * Get filters
     */
    public function getFilters()
    {
        return $this->filters;
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
    
    /**
     * Set showCreateButton
     */
    public function setShowCreateButton($showCreateButton)
    {
        $this->showCreateButton = $showCreateButton;
        return $this;
    }
    
    /**
     * Get showCreateButton
     */
    public function getShowCreateButton()
    {
        return $this->showCreateButton;
    }
}