<?php

namespace AppGear\AppBundle\View;

use AppGear\AppBundle\Entity\View\Field\Widget;

interface WidgetServiceInterface
{
    /**
     * Render custom widget
     *
     * @param object $entity Entity
     * @param Widget $widget Widget
     *
     * @return string
     */
    public function render($entity, Widget $widget);
}