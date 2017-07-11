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
     * @return Model
     */
    protected function getModel()
    {
        return $this->view->getModel();
    }

    /**
     * @return array
     *
     * @todo Merge with DetailViewService::getFields
     */
    protected function getListFields()
    {
        if ([] !== $fields = $this->getFields($this->view->getFields())) {
            return $fields;
        }

        return $this->getFieldsFromModel();
    }

    /**
     * @todo Merge with DetailViewService::getFieldsFromView
     *
     * @param View\Field[] $fields Fields
     *
     * @return array
     */
    protected function getFields(array $fields)
    {
        return array_map(
            function ($field) {
                /** @var View\Field $field */

                $mapping = $field->getMapping();
                $mapping = isset($mapping) ? $mapping : $field->getName();

                if (null !== $mapping) {
                    $parts = \explode('.', $mapping);

                    $currentModel = $this->getModel();
                    foreach ($parts as $part) {
                        $modelService = new ModelService($currentModel);
                        $property     = $modelService->getProperty($part);

                        if ($property instanceof Relationship) {
                            $currentModel = $property->getTarget();
                        }
                    }
                } else {
                    $property = (new ModelService($this->getModel()))->getProperty($field->getName());
                }

                return [
                    'name'     => $field->getName(),
                    'mapping'  => $mapping,
                    'property' => $property,
                    'widget'   => $field->getWidget()
                ];
            },
            $fields
        );
    }

    /**
     * @todo Merge with DetailViewService::getFieldsFromModel
     *
     * @return array
     */
    protected function getFieldsFromModel()
    {
        $modelService = new ModelService($this->getModel());

        return array_map(
            function ($property) {
                return [
                    'name'     => $property->getName(),
                    'mapping'  => $property->getName(),
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

        $this
            ->addData('top', $this->getFields($this->view->getTop()))
            ->addData('fields', $this->getListFields());
    }
}