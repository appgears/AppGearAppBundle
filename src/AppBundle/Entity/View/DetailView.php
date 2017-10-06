<?php

namespace AppGear\AppBundle\Entity\View;

use AppGear\AppBundle\Entity\View;
class DetailView extends View
{
    
    /**
     * Top
     */
    protected $top = array();
    
    /**
     * Fields
     */
    protected $fields = array();
    
    /**
     * Embedded
     */
    protected $embedded = array();
    
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
     * Set embedded
     */
    public function setEmbedded($embedded)
    {
        $this->embedded = $embedded;
        return $this;
    }
    
    /**
     * Get embedded
     */
    public function getEmbedded()
    {
        return $this->embedded;
    }
}