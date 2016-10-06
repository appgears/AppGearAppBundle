<?php

namespace AppGear\AppBundle\EntityService\View;

use AppGear\AppBundle\Entity\View;
use Symfony\Bundle\TwigBundle\TwigEngine;

class ViewService
{
    /**
     * View
     *
     * @var View
     */
    protected $view;

    /**
     * Twig
     *
     * @var TwigEngine
     */
    protected $twig;

    /**
     * Data that will be passed to template
     *
     * @var array
     */
    protected $data = [];

    /**
     * ViewService constructor.
     *
     * @param TwigEngine $twig Twig
     */
    public function __construct(TwigEngine $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Set view
     *
     * @param View $view
     *
     * @return $this
     */
    public function setView(View $view)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Add data that will be passed to template
     *
     * @param string $name  Name for data
     * @param mixed  $value Data
     *
     * @return $this
     */
    protected function addData($name, $value)
    {
        $this->data[$name] = $value;

        return $this;
    }

    /**
     * Collects suitable data for the view rendering
     */
    protected function collectData()
    {
        $this->addData('view', $this->view);
    }

    /**
     * Render template
     *
     * @return string
     *
     * @throws \Twig_Error
     */
    public function render()
    {
        $this->collectData();
        
        return $this->twig->render($this->view->getTemplate(), $this->data);
    }
}