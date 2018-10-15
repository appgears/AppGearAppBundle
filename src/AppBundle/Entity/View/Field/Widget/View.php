<?php

namespace AppGear\AppBundle\Entity\View\Field\Widget;

use AppGear\AppBundle\Entity\View\Field\Widget;
class View extends Widget
{
    
    /**
     * View
     */
    protected $view;
    
    /**
     * DataProvider
     */
    protected $dataProvider;
    
    /**
     * Set view
     */
    public function setView($view)
    {
        $this->view = $view;
        return $this;
    }
    
    /**
     * Get view
     */
    public function getView()
    {
        return $this->view;
    }
    
    /**
     * Set dataProvider
     */
    public function setDataProvider($dataProvider)
    {
        $this->dataProvider = $dataProvider;
        return $this;
    }
    
    /**
     * Get dataProvider
     */
    public function getDataProvider()
    {
        return $this->dataProvider;
    }
}