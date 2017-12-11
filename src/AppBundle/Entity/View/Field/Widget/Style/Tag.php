<?php

namespace AppGear\AppBundle\Entity\View\Field\Widget\Style;

class Tag
{
    
    /**
     * Name
     */
    protected $name;
    
    /**
     * Expression
     */
    protected $expression;
    
    /**
     * Set name
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    /**
     * Get name
     */
    public function getName()
    {
        return $this->name;
    }
    
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