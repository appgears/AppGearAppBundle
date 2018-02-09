<?php

namespace AppGear\AppBundle\Controller;

use AppGear\AppBundle\Entity\Storage\Criteria;
use AppGear\AppBundle\Entity\View;
use AppGear\AppBundle\Entity\View\ListView;
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

        $repository = $this->storage->getRepository($model);

        if (is_scalar($expression)) {
            $criteria = $repository->convertExpression2Criteria($expression);
        } elseif (is_scalar($criteria)) {
            $criteria = $this->storage->getRepository('app.storage.criteria.composite')->find($criteria);
        }

        $filters  = $request->get('filters', []);
        $criteria = $this->applyFilters($criteria, $filters, $view);

        $data = $repository->findBy($criteria, $orderings, $limit, $offset);

        if ($offset !== null) {
            $count = $repository->countBy($criteria);
        }

        return $this->viewResponse($view, compact('request', 'model', 'filters', 'data', 'count', 'page', 'limit', 'offset'));
    }

    /**
     * Merge filters criteria with storage criteria
     *
     * @param Criteria $criteria
     * @param array    $filters
     * @param ListView $listView
     *
     * @return Criteria
     */
    private function applyFilters(Criteria $criteria, array $filters, ListView $listView): Criteria
    {
        /** @var ListView\Filter[] $viewFilters */
        $viewFilters = $listView->getFilters();

        foreach ($filters as $filterIndex) {
            if (!isset($viewFilters[$filterIndex])) {
                continue;
            }

            $viewFilter = $viewFilters[$filterIndex];

            if (null !== $filterCriteria = $viewFilter->getCriteria()) {
                $criteria = (new Criteria\Composite())
                    ->setOperator('AND')
                    ->setExpressions([$criteria, $filterCriteria]);
            }
        }

        return $criteria;
    }
}