<?php

namespace AppGear\AppBundle\Controller;

use AppGear\PlatformBundle\Entity\Model\Property\Field;
use AppGear\PlatformBundle\Entity\Model\Property\Relationship;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ViewController extends AbstractController
{
    /**
     * Action for view
     *
     * @param Request $request
     *
     * @return Response
     */
    public function viewAction(Request $request)
    {
        $viewParameters = $this->requireAttribute($request, '_view');
        $view           = $this->initialize($request, $viewParameters);

        return new Response($this->viewManager->getViewService($view)->render());
    }
}