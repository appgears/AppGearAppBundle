<?php

namespace AppGear\AppBundle\EntityService\View;

use AppGear\AppBundle\Entity\View;
use AppGear\AppBundle\Entity\View\DetailView;
use AppGear\AppBundle\Storage\Storage;
use AppGear\CoreBundle\Model\ModelManager;
use Symfony\Bundle\TwigBundle\TwigEngine;

class DetailViewService extends ViewService
{
    /**
     * Storage
     *
     * @var Storage
     */
    private $storage;

    /**
     * Model manager
     *
     * @var ModelManager
     */
    private $modelManager;

    /**
     * ViewService constructor.
     *
     * @param TwigEngine   $twig    Twig
     * @param Storage      $storage Storage
     * @param ModelManager $modelManager Model manager
     */
    public function __construct(TwigEngine $twig, Storage $storage, ModelManager $modelManager)
    {
        parent::__construct($twig);
        $this->storage      = $storage;
        $this->modelManager = $modelManager;
    }

    public function render()
    {
        /** @var $detailView DetailView */
        $detailView = $this->view;
        $entity     = $detailView->getEntity();

        return $this->twig->render(
            $this->view->getTemplate(),
            [
                'view' => $this->view,
                'entity' => $entity,
                'model' => $this->modelManager->getByInstance($entity)
            ]
        );
    }
}