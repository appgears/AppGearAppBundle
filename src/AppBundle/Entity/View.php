<?php

namespace AppGear\AppBundle\Entity;

class View
{
    
    /**
     * Template
     */
    protected $template = null;
    
    /**
     * UserSpecifiedContent
     */
    protected $userSpecifiedContent = true;
    
    /**
     * Set template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }
    
    /**
     * Get template
     */
    public function getTemplate()
    {
        return $this->template;
    }
    
    /**
     * Set userSpecifiedContent
     */
    public function setUserSpecifiedContent($userSpecifiedContent)
    {
        $this->userSpecifiedContent = $userSpecifiedContent;
        return $this;
    }
    
    /**
     * Get userSpecifiedContent
     */
    public function getUserSpecifiedContent()
    {
        return $this->userSpecifiedContent;
    }
}