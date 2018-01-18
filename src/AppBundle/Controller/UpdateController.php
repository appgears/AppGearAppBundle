<?php

namespace AppGear\AppBundle\Controller;

use AppGear\AppBundle\Security\SecurityManager;
use AppGear\AppBundle\Storage\Storage;
use AppGear\CoreBundle\Entity\Property\Relationship\ToOne;
use AppGear\CoreBundle\Helper\ModelHelper;
use AppGear\CoreBundle\Model\ModelManager;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyAccess\PropertyAccessor;
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
        // Check EDIT permission to entity
        if (!$this->securityManager->check(BasicPermissionMap::PERMISSION_EDIT, $model)) {
            throw new AccessDeniedHttpException();
        }

        // Load model and entity from storage
        $model  = $this->modelManager->get($model);
        $entity = $this->storage->find($model, $id);

        // If entity not found
        if (null === $entity) {
            throw new NotFoundHttpException();
        }

        $accessor = new PropertyAccessor();

        // Iterate over each POST parameters
        foreach ($request->request->all() as $name => $value) {

            // Load suitable model property by POST-parameter name
            $property = ModelHelper::getProperty($model, $name);

            if ($property instanceof ToOne) {
                /* Property is to-one relationship */
                $targetModel = $property->getTarget();

                // Check view permission to target relationship model
                if (!$this->securityManager->check(BasicPermissionMap::PERMISSION_VIEW, (string) $targetModel, $value)) {
                    throw new AccessDeniedHttpException();
                }

                // Load target relationship model entity with identifier from POST-parameter value
                $targetEntity = $this->storage->find($targetModel, $value);

                // If entity not found
                if (null === $targetEntity) {
                    throw new NotFoundHttpException();
                }

                // Update relationship value
                $accessor->setValue($entity, $name, $targetEntity);
            } else {
                // TODO: implement support for all property types
                throw new RuntimeException('Passed parameter type does not implemented');
            }
        }

        // Save entity
        $this->storage->save($entity);

        return new Response;
    }
}