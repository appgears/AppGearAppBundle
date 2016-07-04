<?php

namespace AppGear\AppBundle\Event;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class HttpNotFoundRedirect
{
    /**
     * Holds Symfony2 router
     *
     * @var Router
     */
    protected $router;

    /**
     * Route name for redirect
     *
     * @var string
     */
    private $route;

    /**
     * Enabled redirect
     *
     * @var bool
     */
    private $enabled;

    /**
     * @param Router  $router  Router
     * @param boolean $enabled Enabled redirect
     * @param string  $route   Route name for redirect
     */
    public function __construct(Router $router, $enabled, $route)
    {
        $this->router  = $router;
        $this->enabled = $enabled;
        $this->route   = $route;
    }

    /**
     * OnKernelException listener
     *
     * @param GetResponseForExceptionEvent $event Event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (!$this->enabled) {
            return;
        }

        $exception = $event->getException();
        if ($exception instanceof NotFoundHttpException) {
            if ($this->route === $event->getRequest()->get('_route')) {
                return;
            }

            $url      = $this->router->generate($this->route);
            $response = new RedirectResponse($url, 301);
            $event->setResponse($response);
        }
    }
}
