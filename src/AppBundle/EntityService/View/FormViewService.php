<?php

namespace AppGear\AppBundle\EntityService\View;

use AppGear\AppBundle\Entity\View;
use AppGear\AppBundle\Entity\View\FormView;

class FormViewService extends ViewService
{
    /**
     * {@inheritdoc}
     */
    public function collectData()
    {
        parent::collectData();

        /** @var FormView $view */
        $view = $this->view;
        $this->addData('form', $view->getForm()->createView());
    }
}