<?php

namespace AppGear\AppBundle\View\Dto;

use AppGear\CoreBundle\Entity\Property\Relationship;

/**
 * Dto для передачи контекста родительского view при рендере дочернего view
 */
class ViewContextDto
{
    /**
     * Сущность которая ссылается (relation) на коллекцию, для которой рендерится список
     *
     * @var object
     */
    public $entity;

    /**
     * Свойство-связь сущности, по которой берется коллекция для рендеринга
     *
     * @var Relationship
     */
    public $relationship;
}