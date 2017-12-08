<?php

namespace AppGear\AppBundle\Entity\View\Field\Transformer;

use AppGear\AppBundle\Entity\View\Field\Transformer;
class Expression extends Transformer
{
    
    /**
     * Expression
     */
    protected $expression;
    
    /**
     * Set expression
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;
        return $this;
    }
    
    /**
     * Get expression
     */
    public function getExpression()
    {
        return $this->expression;
    }
}