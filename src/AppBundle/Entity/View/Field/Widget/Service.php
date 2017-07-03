<?php

namespace AppGear\AppBundle\Entity\View\Field\Widget;

use AppGear\AppBundle\Entity\View\Field\Widget;
class Service extends Widget
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