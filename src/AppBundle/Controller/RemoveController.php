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
        $modelId = $this->requireAttribute($request, '_model');
        $modelId = $this->performExpression($request, $modelId);
        $model   = $this->modelManager->get($modelId);

        if (!$request->request->has('id')) {
            throw new BadRequestHttpException('Undefined id parameter');
        }
        $id = $request->request->get('id');

        $repository = $this->storage->getRepository($model);
        $entity = $repository->find($id);
        $repository->remove($entity);

        if ($redirect = $this->buildRedirectResponse($request)) {
            return $redirect;
        }
        return new Response('Deleted!');
    }
}