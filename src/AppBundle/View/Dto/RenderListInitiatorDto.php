<?php

namespace AppGear\AppBundle\View\Dto;

use AppGear\CoreBundle\Entity\Property\Relationship;
use Doctrine\Common\Collections\Collection;
use Traversable;

/**
 * Dto-инициатора рендеринга списка
 */
class RenderListInitiatorDto
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