<?php

namespace AppGear\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RemoveController extends AbstractController
{
    /**
     * Action for entity removing
     *
     * @param Request $request Request
     *
     * @return Response
     */
    public function removeAction(Request $request)
    {
        $modelId = $this->requireAttribute($request, 'model');
        $modelId = $this->performExpression($request, $modelId);
        $model   = $this->modelManager->get($modelId);

        if (!$request->attributes->has('id')) {
            throw new BadRequestHttpException('Undefined id parameter');
        }
        $id = $request->attributes->get('id');

        $entity = $this->storage->find($model, $id);
        $this->storage->remove($entity);

        if ($redirect = $this->buildRedirectResponse($request)) {
            return $redirect;
        }
        return new Response();
    }
}