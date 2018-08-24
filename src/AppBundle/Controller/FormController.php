<?php

namespace AppGear\AppBundle\Controller;

use AppGear\AppBundle\Entity\View;
use AppGear\AppBundle\Form\FormBuilder;
use AppGear\AppBundle\Form\FormManager;
use AppGear\AppBundle\Security\SecurityManager;
use AppGear\AppBundle\Storage\Storage;
use AppGear\AppBundle\View\ViewManager;
use AppGear\CoreBundle\Model\ModelManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Acl\Permission\BasicPermissionMap;

class FormController extends AbstractController
{
    /**
     * Model form builder
     *
     * @var FormBuilder
     */
    protected $formBuilder;

    /**
     * @var FormManager
     */
    protected $formManager;

    /**
     * CrudController constructor.
     *
     * @param Storage         $storage         Storage
     * @param ModelManager    $modelManager    Model manager
     * @param ViewManager     $viewManager     View manager
     * @param SecurityManager $securityManager Security manager
     * @param FormManager     $formManager     Form builder for model
     */
    public function __construct(
        Storage $storage,
        ModelManager $modelManager,
        ViewManager $viewManager,
        SecurityManager $securityManager,
        FormManager $formManager
    ) {
        parent::__construct($storage, $modelManager, $viewManager, $securityManager);

        $this->formManager = $formManager;
    }

    /**
     * Action for form view and process
     *
     * @param Request $request Request
     * @param string  $model   Model name
     * @param mixed   $id      Entity ID
     *
     * @return Response
     */
    public function formAction(Request $request, $model, $id = null)
    {
        $repository = $this->storage->getRepository($model);

        $model  = $this->modelManager->get($model);
        $entity = ($id === null) ? $repository->create() : $repository->find($id);

        $this->checkAccess((string)$model, $id);

        $this->formManager->build($model, $entity)->getSymfonyFormBuilder();
        $submitResult = $this->formManager->submit($request, $model);

        if ($submitResult->isSubmitted && $submitResult->isValid) {
            $this->storage->getRepository($model)->save($entity);

            return $this->response($request, $model, $entity);
        }

        /** @var View $view */
        $view = $this->initialize($request, $this->requireAttribute($request, 'view'));

        return $this->viewResponse($view, ['form' => $this->formManager->createView()]);
    }

    /**
     * Check access
     *
     * @param string $model
     * @param mixed  $id ID
     */
    public function checkAccess($model, $id)
    {
        $permission = ($id === null) ? BasicPermissionMap::PERMISSION_CREATE : BasicPermissionMap::PERMISSION_EDIT;

        if (!$this->securityManager->check($permission, $model)) {
            throw new AccessDeniedHttpException();
        }
    }
}