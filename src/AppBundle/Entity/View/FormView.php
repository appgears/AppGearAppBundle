<?php

namespace AppGear\AppBundle\Entity\View;

use AppGear\AppBundle\Entity\View;
class FormView extends View
{
    
    /**
     * Form
     */
    protected $form;
    
    /**
     * Fields
     */
    protected $fields = array();
    
    /**
     * Set form
     */
    public function setForm($form)
    {
        $this->form = $form;
        return $this;
    }
    
    /**
     * Get form
     */
    public function getForm()
    {
        return $this->form;
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