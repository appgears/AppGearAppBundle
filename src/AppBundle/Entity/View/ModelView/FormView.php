<?php

namespace AppGear\AppBundle\Entity\View\ModelView;

use AppGear\AppBundle\Entity\View;
class FormView extends View
{
    
    /**
     * Form
     */
    protected $form;
    
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
}