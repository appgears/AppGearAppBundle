<?php

namespace AppGear\AppBundle\Controller;

use AppGear\AppBundle\Security\SecurityManager;
use AppGear\AppBundle\Storage\Storage;
use AppGear\CoreBundle\Helper\ModelHelper;
use AppGear\CoreBundle\Model\ModelManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Acl\Permission\BasicPermissionMap;

class UpdateController extends Controller
{
    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var SecurityManager
     */
    private $securityManager;
    /**
     * @var ModelManager
     */
    private $modelManager;

    public function __construct(Storage $storage, SecurityManager $securityManager, ModelManager $modelManager)
    {
        $this->storage         = $storage;
        $this->securityManager = $securityManager;
        $this->modelManager    = $modelManager;
    }

    /**
     * Update action
     *
     * @param Request    $request Request
     * @param string     $model
     * @param int|string $id
     *
     * @return Response
     */
    public function updateAction(Request $request, string $model, $id)
    {
        if (!$this->securityManager->check(BasicPermissionMap::PERMISSION_EDIT, $model)) {
            throw new AccessDeniedHttpException();
        }

        $model  = $this->modelManager->get($model);
        $entity = $this->storage->find($model, $id);

        if (null === $entity) {
            throw new NotFoundHttpException();
        }

        foreach ($request->request->all() as $name => $value) {
            $property = ModelHelper::getProperty($model, $name);

            if ($property instanceof Property\Field) {

            } else {
                throw new RuntimeException('Passed parameter type does not implemented');
            }
        }

        return new Response;
    }
}