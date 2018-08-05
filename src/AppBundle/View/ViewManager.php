<?php

namespace AppGear\AppBundle\View;

use AppGear\AppBundle\Entity\View;
use Twig_Environment;

class ViewManager
{
    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * ViewManager constructor.
     *
     * @param Twig_Environment $twig
     */
    public function __construct(Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Find and return service for view
     *
     * @param View $view View
     *
     * @return string
     */
    public function render(View $view, array $data = []): string
    {
        $data = array_merge(['view' => $view], $data);

        return $this->twig->render($view->getTemplate(), $data);
    }

    public function serialize(View $view, $data = null): array
    {
        return [];
    }
}