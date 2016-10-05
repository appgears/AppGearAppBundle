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
     * {@inheritdoc}
     */
    public function collectData()
    {
        parent::collectData();

        /** @var DetailView $view */
        $view   = $this->view;
        $entity = $view->getEntity();
        $model  = $this->modelManager->getByInstance($entity);

        $this
            ->addData('entity', $entity)
            ->addData('model', $model);
    }
}