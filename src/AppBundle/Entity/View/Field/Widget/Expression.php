<?php

namespace AppGear\AppBundle\Entity\View\Field\Widget;

use AppGear\AppBundle\Entity\View\Field\Widget;
class Expression extends Widget
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