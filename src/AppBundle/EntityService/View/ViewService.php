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

    public function render()
    {
        return $this->twig->render($this->view->getTemplate(), ['view' => $this->view]);
    }
}