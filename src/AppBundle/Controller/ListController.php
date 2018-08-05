<?php

namespace AppGear\AppBundle\Controller;

use AppGear\AppBundle\Entity\Storage\Criteria;
use AppGear\AppBundle\Entity\View;
use AppGear\AppBundle\Entity\View\ListView;
use AppGear\AppBundle\Form\FormManager;
use AppGear\AppBundle\Security\SecurityManager;
use AppGear\AppBundle\Storage\Storage;
use AppGear\AppBundle\View\ViewManager;
use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Helper\ModelHelper;
use AppGear\CoreBundle\Helper\PropertyHelper;
use AppGear\CoreBundle\Model\ModelManager;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ListController extends AbstractController
{
    /**
     * @var FormManager
     */
    private $formManager;

    public function __construct(Storage $storage, ModelManager $modelManager, ViewManager $viewManager, SecurityManager $securityManager, FormManager $formManager)
    {
        parent::__construct($storage, $modelManager, $viewManager, $securityManager);

        $this->formManager = $formManager;
    }

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
        $model = $this->modelManager->get($model);

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

        $page   = $request->get('page');
        $limit  = $request->get('limit');
        $offset = $count = null;

        if (null !== $limit && null !== $page) {
            $offset = ((int) $page - 1) * $limit;
        }

        $repository = $this->storage->getRepository($model);

        if (is_scalar($expression)) {
            $criteria = $repository->convertExpression2Criteria($expression);
        } elseif (is_scalar($criteria)) {
            $criteria = $this->storage->getRepository('app.storage.criteria.composite')->find($criteria);
        } elseif (null === $criteria) {
            $criteria = new Criteria\Composite();
        }

        /*$filters  = $request->get('filters', []);
        $criteria = $this->applyCriteriaFilters($criteria, $filters, $view);*/

        $filtersForm     = $this->buildFiltersForm($model, $view)->getForm();
        $criteria        = $this->applyFilterForm($request, $view, $filtersForm, $criteria);
        $filtersFormView = $filtersForm->createView();

        $data = $repository->findBy($criteria, $orderings, $limit, $offset);

        if ($offset !== null) {
            $count = $repository->countBy($criteria);
        }

        $viewData = compact('model', 'filtersFormView', 'data', 'count', 'page', 'limit', 'offset');
        $format   = $request->attributes->get('format');
        $format   = ltrim($format, '.');

        return $this->viewResponse($view, $viewData, $format);
    }

    /**
     * Build form for filters
     *
     * @param Model    $model    Model
     * @param ListView $listView List view
     *
     * @return FormBuilderInterface
     */
    private function buildFiltersForm(Model $model, ListView $listView)
    {
        $appFormBuilder     = $this->formManager->getFormBuilder();
        $symfonyFormBuilder = $appFormBuilder->create(null, ['csrf_protection' => false]);
        $symfonyFormBuilder->setMethod('GET');

        /** @var ListView\Filter $filter */
        foreach ($listView->getFilters() as $filter) {
            $mapping  = $filter->getMapping() ?? $filter->getName();
            $property = ModelHelper::getProperty($model, $mapping);

            // Input
            if (PropertyHelper::isField($property)) {
                list($type, $options) = $appFormBuilder->resolveFieldType($property);
            } else {
                list($type, $options) = $appFormBuilder->resolveRelationType($property);

                $options['multiple'] = true;
            }

            $symfonyFormBuilder->add($filter->getName(), $type, $options);

            // Negative checkbox
            $symfonyFormBuilder->add($filter->getName() . '_' . 'negative', 'checkbox');
        }

        return $symfonyFormBuilder;
    }

    /**
     * Add filters criteria from submitted from to storage criteria
     *
     * @param Request       $request  Request object
     * @param FormInterface $form     Symfony form instance
     * @param Criteria      $criteria Storage criteria
     *
     * @return Criteria|Criteria\Composite
     */
    private function applyFilterForm(Request $request, ListView $view, FormInterface $form, Criteria $criteria)
    {
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return $criteria;
        }

        $data = $form->getData();

        if (count($data) === 0) {
            return $criteria;
        }

        $expressions = [];
        foreach ($view->getFilters() as $filter) {
            $name    = $filter->getName();
            $mapping = $filter->getMapping() ?? $name;

            if (!isset($data[$name])) {
                continue;
            }

            $value = $data[$name];

            if (is_array($value) && count($value) === 0) {
                continue;
            }

            $isNegative = $data[$name . '_negative'];
            if (is_array($value)) {
                $comparison = $isNegative ? 'nin' : 'in';
            } else {
                $comparison = $isNegative ? 'neq' : 'eq';
            }

            $expression = new Criteria\Expression();
            $expression
                ->setField($mapping)
                ->setComparison($comparison)
                ->setValue($value);

            $expressions[] = $expression;
        }

        if ($criteria instanceof Criteria\Composite && $criteria->getOperator() === 'AND') {
            $criteria->setExpressions(array_merge($criteria->getExpressions(), $expressions));
        } else {
            $criteria = (new Criteria\Composite())->setExpressions(array_merge([$criteria], $expressions));
        }

        return $criteria;
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
    private function applyCriteriaFilters(Criteria $criteria, array $filters, ListView $listView): Criteria
    {
        /** @var ListView\Filter[] $viewFilters */
        $viewFilters = $listView->getFilters();

        foreach ($filters as $filterIndex) {
            if (!isset($viewFilters[$filterIndex])) {
                continue;
            }

            $viewFilter = $viewFilters[$filterIndex];

            if (null !== $filterCriteria = $viewFilter->getCriteria()) {
                $criteria = new Criteria\Composite();
                $criteria->setExpressions([$criteria, $filterCriteria]);
            }
        }

        return $criteria;
    }
}