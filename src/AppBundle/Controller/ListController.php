<?php

namespace AppGear\AppBundle\Controller;

use AppGear\AppBundle\Entity\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ListController extends AbstractController
{
    /**
     * Action for list view
     *
     * @param Request $request
     * @param string  $model
     *
     * @return Response
     */
    public function listAction(Request $request, string $model): Response
    {
        $viewParameters = $this->requireAttribute($request, 'view');

        if (is_scalar($viewParameters)) {
            /** @var View $view */
            $view = $this->storage->find('app.view.list_view', $viewParameters);
        } else {
            /** @var View $view */
            $view = $this->initialize($request, $viewParameters);
        }

        $expression = $request->get('data[expression]', null, true);
        $criteria   = $request->get('data[criteria]', null, true);
        $orderings  = $request->get('data[orderings]', [], true);

        $page   = $request->get('page', null);
        $limit  = $request->get('limit', null);
        $offset = $count = null;

        if (null !== $limit && null !== $page) {
            $offset = ((int) $page - 1) * $limit;
        }

        if (is_scalar($expression)) {
            $data = $this->storage->getRepository($model)->findByExpr($expression, $orderings, $limit, $offset);

            if ($offset !== null) {
                $count = $this->storage->getRepository($model)->countByExpr($expression);
            }
        } elseif (is_scalar($criteria)) {
            $criteria = $this->storage->getRepository('app.storage.criteria.composite')->find($criteria);
        } else {
            $data = $this->storage->getRepository($model)->findBy([], $orderings, $limit, $offset);

            if ($offset !== null) {
                $count = $this->storage->getRepository($model)->countBy([]);
            }
        }

        return $this->viewResponse($view, compact('request', 'model', 'data', 'count', 'page', 'limit', 'offset'));
    }
}