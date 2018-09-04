<?php

namespace AppGear\AppBundle\View\Handler;

use AppGear\AppBundle\Entity\View\ListView;

interface ListHandlerInterface
{
    public function prepareView(ListView $view);
}