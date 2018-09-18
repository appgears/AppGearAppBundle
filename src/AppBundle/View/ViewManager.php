<?php

namespace AppGear\AppBundle\View;

use AppGear\AppBundle\View\Handler\ListHandler;
use AppGear\AppBundle\Entity\View;
use Twig_Environment;

class ViewManager
{
    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * @var ListHandler
     */
    private $listHandler;

    /**
     * ViewManager constructor.
     *
     * @param Twig_Environment $twig
     * @param ListHandler      $listHandler
     */
    public function __construct(Twig_Environment $twig, ListHandler $listHandler)
    {
        $this->twig        = $twig;
        $this->listHandler = $listHandler;
    }

    /**
     * @param View $view View
     *
     * @return string
     */
    public function render(View $view, array $data = []): string
    {
        if (is_a($view, View\ListView::class)) {
            /** @var $view View\ListView */
            return $this->renderList($view, $data);
        }

        return $this->renderView($view, $data);
    }

    /**
     * @param View  $view
     * @param array $data
     *
     * @return string
     */
    public function renderView(View $view, array $data = []): string
    {
        $data['view'] = $view;

        return $this->twig->render($view->getTemplate(), $data);
    }

    /**
     * @param View\ListView $listView
     * @param array         $data
     *
     * @return string
     */
    public function renderList(View\ListView $listView, array $data = []): string
    {
        $this->listHandler->prepareView($listView);

        return $this->renderView($listView, $data);
    }

    /**
     * @param View $view
     * @param null $data
     *
     * @return array
     */
    public function serialize(View $view, $data = null): array
    {
        return [];
    }
}