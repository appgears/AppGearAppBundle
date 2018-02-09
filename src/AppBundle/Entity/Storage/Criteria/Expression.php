<?php

namespace AppGear\AppBundle\Entity\Storage\Criteria;

use AppGear\AppBundle\Entity\Storage\Criteria;
class Expression extends Criteria
{
    
    /**
     * Field
     */
    protected $field;
    
    /**
     * Comparison
     */
    protected $comparison = 'eq';
    
    /**
     * Value
     */
    protected $value;
    
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
    
    /**
     * Set comparison
     */
    public function setComparison($comparison)
    {
        $this->comparison = $comparison;
        return $this;
    }
    
    /**
     * Get comparison
     */
    public function getComparison()
    {
        return $this->comparison;
    }
    
    /**
     * Set value
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
    
    /**
     * Get value
     */
    public function getValue()
    {
        return $this->value;
    }
}