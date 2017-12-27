<?php

namespace AppGear\AppBundle\Controller;

use AppGear\AppBundle\Entity\View;
use InvalidArgumentException;
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
    public function viewAction(Request $request): Response
    {
        $viewParameters = $this->requireAttribute($request, 'view');

        if (is_scalar($viewParameters)) {
            /** @var View $view */
            $view = $this->storage->find('app.view', $viewParameters);
        } else {
            /** @var View $view */
            $view = $this->initialize($request, $viewParameters);
        }

        if (null !== $data = $request->get('data')) {

            $model = $data['model'] ?? null;
            if ($model === null) {
                throw new InvalidArgumentException('ViewController: "data" parameter does not contain "model" parameter');
            }
            $model = $this->performExpression($request, $model);

            $id         = $data['id'] ?? null;
            $expression = $data['expression'] ?? null;
            $orderings  = $data['orderings'] ?? [];

            if ($id !== null) {
                $id   = $this->performExpression($request, $id);
                $data = $this->storage->find($model, $id);
            } elseif ($expression !== null) {
                $expression = $this->performExpression($request, $expression);
                $data       = $this->storage->getRepository($model)->findByExpr($expression, $orderings);
            } else {
                $data = $this->storage->getRepository($model)->findBy([], $orderings);
            }
        }

        return $this->viewResponse($view, compact('data'));
    }
}