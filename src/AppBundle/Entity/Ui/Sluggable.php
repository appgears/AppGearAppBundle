<?php

namespace AppGear\AppBundle\Entity\Ui;

use AppGear\CoreBundle\Entity\Extension\Property\Computed;
class Sluggable extends Computed
{
    
    /**
     * Field
     */
    protected $field;
    
    /**
     * Set field
     */
    public function setField($field)
    {
        $this->field = $field;
        return $this;
    }
    
    /**
     * Get field
     */
    public function getField()
    {
        return $this->field;
    }
}