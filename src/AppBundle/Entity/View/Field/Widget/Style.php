<?php

namespace AppGear\AppBundle\Entity\View\Field\Widget;

use AppGear\AppBundle\Entity\View\Field\Widget;
class Style extends Widget
{
    
    /**
     * Tags
     */
    protected $tags = array();
    
    /**
     * Set tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
        return $this;
    }
    
    /**
     * Get tags
     */
    public function getTags()
    {
        return $this->tags;
    }
}