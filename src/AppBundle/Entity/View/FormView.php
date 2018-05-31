<?php

namespace AppGear\AppBundle\Entity\View;

use AppGear\AppBundle\Entity\View;
class FormView extends View
{
    
    /**
     * Fields
     */
    protected $fields = array();
    
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