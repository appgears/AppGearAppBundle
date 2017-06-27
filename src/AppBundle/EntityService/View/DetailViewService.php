<?php

namespace AppGear\AppBundle\EntityService\View;

use AppGear\AppBundle\Entity\View;
use AppGear\AppBundle\Entity\View\DetailView;
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
        $fields = [];

        return $fields;
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
            ->addData('fields', $this)
        ;
    }
}