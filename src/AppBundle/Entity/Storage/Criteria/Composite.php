<?php

namespace AppGear\AppBundle\Entity\Storage\Criteria;

use AppGear\AppBundle\Entity\Storage\Criteria;
class Composite extends Criteria
{
    
    /**
     * Operator
     */
    protected $operator;
    
    /**
     * Expressions
     */
    protected $expressions = array();
    
    /**
     * Set operator
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
        return $this;
    }
    
    /**
     * Get operator
     */
    public function getOperator()
    {
        return $this->operator;
    }
    
    /**
     * Set expressions
     */
    public function setExpressions($expressions)
    {
        $this->expressions = $expressions;
        return $this;
    }
    
    /**
     * Get expressions
     */
    public function getExpressions()
    {
        return $this->expressions;
    }
}