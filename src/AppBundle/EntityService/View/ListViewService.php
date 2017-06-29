<?php

namespace AppGear\AppBundle\EntityService\View;

use AppGear\AppBundle\Entity\View;
use AppGear\AppBundle\Entity\View\DetailView;
use AppGear\CoreBundle\EntityService\ModelService;
use AppGear\CoreBundle\Model\ModelManager;
use Symfony\Bundle\TwigBundle\TwigEngine;

class ListViewService extends ViewService
{
    /**
     * @return array
     *
     * @todo Merge with DetailViewService::getFields
     */
    protected function getFields()
    {
        $modelService = new ModelService($this->view->getModel());

        if ([] !== $fields = $this->getFieldsFromView($modelService)) {
            return $fields;
        }

        return $this->getFieldsFromModel($modelService);
    }

    /**
     * @todo Merge with DetailViewService::getFieldsFromView
     *
     * @param ModelService $modelService
     *
     * @return array
     */
    protected function getFieldsFromView(ModelService $modelService)
    {
        return array_map(
            function ($field) use ($modelService) {
                /** @var View\Field $field */
                return [
                    'name'     => $field->getName(),
                    'property' => $modelService->getProperty($field->getName()),
                    'widget'   => $field->getWidget()
                ];
            },
            $this->view->getFields()
        );
    }

    /**
     * @todo Merge with DetailViewService::getFieldsFromModel
     *
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

        $this->addData('fields', $this->getFields());
    }
}