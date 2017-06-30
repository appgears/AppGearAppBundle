<?php

namespace AppGear\AppBundle\EntityService\View;

use AppGear\AppBundle\Entity\View;
use AppGear\AppBundle\Entity\View\DetailView;
use AppGear\CoreBundle\Entity\Property\Relationship;
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

        if ([] !== $fields = $this->getFieldsFromView()) {
            return $fields;
        }

        return $this->getFieldsFromModel($modelService);
    }

    /**
     * @todo Merge with DetailViewService::getFieldsFromView
     *
     * @return array
     */
    protected function getFieldsFromView()
    {
        return array_map(
            function ($field) {
                /** @var View\Field $field */

                $mapping = $field->getMapping();
                $mapping = isset($mapping) ? $mapping : $field->getName();

                if (null !== $mapping) {
                    $parts = \explode('.', $mapping);

                    $currentModel = $this->view->getModel();
                    foreach ($parts as $part) {
                        $modelService = new ModelService($currentModel);
                        $property     = $modelService->getProperty($part);

                        if ($property instanceof Relationship) {
                            $currentModel = $property->getTarget();
                        }
                    }
                } else {
                    $property = (new ModelService($this->view->getModel()))->getProperty($field->getName());
                }

                return [
                    'name'     => $field->getName(),
                    'mapping'  => $mapping,
                    'property' => $property,
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