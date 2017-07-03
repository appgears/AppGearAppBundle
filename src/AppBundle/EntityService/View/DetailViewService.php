<?php

namespace AppGear\AppBundle\EntityService\View;

use AppGear\AppBundle\Entity\View;
use AppGear\AppBundle\Entity\View\DetailView;
use AppGear\CoreBundle\EntityService\ModelService;
use AppGear\CoreBundle\Model\ModelManager;
use Symfony\Bundle\TwigBundle\TwigEngine;

class DetailViewService extends ViewService
{
    /**
     * Model manager
     *
     * @var ModelManager
     */
    protected $modelManager;

    /**
     * ViewService constructor.
     *
     * @param TwigEngine   $twig         Twig
     * @param ModelManager $modelManager Model manager
     */
    public function __construct(TwigEngine $twig, ModelManager $modelManager)
    {
        parent::__construct($twig);

        $this->modelManager = $modelManager;
    }

    /**
     * Get entity from view
     *
     * @return object
     */
    protected function getEntity()
    {
        /** @var DetailView $view */
        $view = $this->view;

        return $view->getEntity();
    }

    /**
     * @return array
     */
    protected function getFields()
    {
        $entity       = $this->getEntity();
        $model        = $this->modelManager->getByInstance($entity);
        $modelService = new ModelService($model);

        if ([] !== $fields = $this->getFieldsFromView($modelService)) {
            return $fields;
        }

        return $this->getFieldsFromModel($modelService);
    }

    /**
     * @param ModelService $modelService
     *
     * @return array
     */
    protected function getFieldsFromView(ModelService $modelService)
    {
        return array_map(
            function ($field) use ($modelService) {
                /** @var View\Field $field */

                $mapping = $field->getMapping();
                $mapping = isset($mapping) ? $mapping : $field->getName();

                if (null !== $mapping) {
                    $parts = \explode('.', $mapping);

                    $currentModel = $this->modelManager->getByInstance($this->getEntity());
                    foreach ($parts as $part) {
                        $modelService = new ModelService($currentModel);
                        $property     = $modelService->getProperty($part);

                        if ($property instanceof Relationship) {
                            $currentModel = $property->getTarget();
                        }
                    }
                } else {
                    $model = $this->modelManager->getByInstance($this->getEntity());
                    $property = (new ModelService($model))->getProperty($field->getName());
                }

                return [
                    'name'     => $field->getName(),
                    'mapping'  => $mapping,
                    'property' => $modelService->getProperty($field->getName()),
                    'widget'   => $field->getWidget()
                ];
            },
            $this->view->getFields()
        );
    }

    /**
     * @param ModelService $modelService
     *
     * @return array
     */
    protected function getFieldsFromModel(ModelService $modelService)
    {
        return array_map(
            function ($property) {
                return [
                    'name'     => $property->getName(),
                    'property' => $property
                ];
            },
            $modelService->getAllProperties()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function collectData()
    {
        parent::collectData();

        $entity = $this->getEntity();
        $model  = $this->modelManager->getByInstance($entity);

        $this
            ->addData('model', $model)
            ->addData('entity', $entity)
            ->addData('fields', $this->getFields());
    }
}