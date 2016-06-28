<?php

namespace AppGear\AppBundle\Entity\View\ModelView;

use AppGear\AppBundle\Entity\View\ModelView;
class DetailView extends ModelView
{
    
    /**
     * Id
     */
    protected $id;
    
    /**
     * Set id
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    
    /**
     * Get id
     */
    public function getId()
    {
        return $this->id;
    }
}