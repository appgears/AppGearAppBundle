<?php

namespace AppGear\AppBundle\View\Handler;

use AppGear\AppBundle\Entity\View\ListView;
use AppGear\AppBundle\Security\SecurityManager;
use Symfony\Component\Security\Acl\Permission\BasicPermissionMap;

class ListHandler implements ListHandlerInterface
{
    /**
     * @var SecurityManager
     */
    private $securityManager;

    /**
     * ListHandler constructor.
     *
     * @param SecurityManager $securityManager
     */
    public function __construct(SecurityManager $securityManager)
    {
        $this->securityManager = $securityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareView(ListView $view)
    {
        $canCreate = $this->securityManager->check(BasicPermissionMap::PERMISSION_CREATE, $view->getModel());
        $view->setShowCreateButton($canCreate);
    }
}