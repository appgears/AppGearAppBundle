<?php

namespace AppGear\AppBundle\Entity\View;

use AppGear\AppBundle\Entity\View;
class ModelView extends View
{
    
    /**
     * Model
     */
    protected $model;
    
    /**
     * Set model
     */
    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }
    
    /**
     * Get model
     */
    public function getModel()
    {
        return $this->model;
    }
}