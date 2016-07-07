<?php

namespace AppGear\AppBundle\EntityService\View;

use AppGear\AppBundle\Entity\View;

class FormViewService extends ViewService
{
    public function render()
    {
        return $this->twig->render(
            $this->view->getTemplate(),
            [
                'view' => $this->view,
                'form' => $this->view->getForm()->createView()
            ]
        );
    }
}