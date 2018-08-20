<?php

namespace AppGear\AppBundle\Controller;

use AppGear\AppBundle\Entity\View;
use AppGear\AppBundle\Form\FormBuilder;
use AppGear\AppBundle\Form\FormManager;
use AppGear\AppBundle\Security\SecurityManager;
use AppGear\AppBundle\Storage\Storage;
use AppGear\AppBundle\View\ViewManager;
use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Model\ModelManager;
use Psr\Log\LoggerInterface;
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CrudController constructor.
     *
     * @param Storage         $storage         Storage
     * @param ModelManager    $modelManager    Model manager
     * @param ViewManager     $viewManager     View manager
     * @param SecurityManager $securityManager Security manager
     * @param FormManager     $formManager     Form builder for model
     * @param LoggerInterface $logger          Logger
     */
    public function __construct(
        Storage $storage,
        ModelManager $modelManager,
        ViewManager $viewManager,
        SecurityManager $securityManager,
        FormManager $formManager,
        LoggerInterface $logger
    ) {
        parent::__construct($storage, $modelManager, $viewManager, $securityManager);

        $this->formManager = $formManager;
        $this->logger      = $logger;
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

        $this->checkAccess((string) $model, $id);

        $formBuilder = $this->formManager->getBuilder($model, $entity);
        $form        = $formBuilder->getForm();

        if ($this->formManager->submit($formBuilder, $form, $request, $model)) {
            $this->storage->getRepository($model)->save($entity);


            return $this->response($request, $model, $entity);
        }

        if (!$form->isValid()) {
            $this->logger->error((string) $form->getErrors(true));
        }

        /** @var View $view */
        $view = $this->initialize($request, $this->requireAttribute($request, 'view'));

        return $this->viewResponse($view, ['form' => $form->createView()]);
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