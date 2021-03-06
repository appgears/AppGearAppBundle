<?php

namespace AppGear\AppBundle\Entity\View;

class Field
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
     * Transformer
     */
    protected $transformer;
    
    /**
     * Widget
     */
    protected $widget;
    
    /**
     * Group
     */
    protected $group;
    
    /**
     * LiveEdit
     */
    protected $liveEdit = false;
    
    /**
     * Exclude
     */
    protected $exclude;
    
    /**
     * CheckAccess
     */
    protected $checkAccess = false;
    
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
     * Set transformer
     */
    public function setTransformer($transformer)
    {
        $this->transformer = $transformer;
        return $this;
    }
    
    /**
     * Get transformer
     */
    public function getTransformer()
    {
        return $this->transformer;
    }
    
    /**
     * Set widget
     */
    public function setWidget($widget)
    {
        $this->widget = $widget;
        return $this;
    }
    
    /**
     * Get widget
     */
    public function getWidget()
    {
        return $this->widget;
    }
    
    /**
     * Set group
     */
    public function setGroup($group)
    {
        $this->group = $group;
        return $this;
    }
    
    /**
     * Get group
     */
    public function getGroup()
    {
        return $this->group;
    }
    
    /**
     * Set liveEdit
     */
    public function setLiveEdit($liveEdit)
    {
        $this->liveEdit = $liveEdit;
        return $this;
    }
    
    /**
     * Get liveEdit
     */
    public function getLiveEdit()
    {
        return $this->liveEdit;
    }
    
    /**
     * Set exclude
     */
    public function setExclude($exclude)
    {
        $this->exclude = $exclude;
        return $this;
    }
    
    /**
     * Get exclude
     */
    public function getExclude()
    {
        return $this->exclude;
    }
    
    /**
     * Set checkAccess
     */
    public function setCheckAccess($checkAccess)
    {
        $this->checkAccess = $checkAccess;
        return $this;
    }
    
    /**
     * Get checkAccess
     */
    public function getCheckAccess()
    {
        return $this->checkAccess;
    }
}