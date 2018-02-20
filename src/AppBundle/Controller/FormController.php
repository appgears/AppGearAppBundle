<?php

namespace AppGear\AppBundle\Controller;

use AppGear\AppBundle\Entity\View;
use AppGear\AppBundle\Form\FormBuilder;
use AppGear\AppBundle\Form\FormManager;
use AppGear\AppBundle\Helper\StorageHelper;
use AppGear\AppBundle\Security\SecurityManager;
use AppGear\AppBundle\Storage\Storage;
use AppGear\AppBundle\View\ViewManager;
use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Helper\ModelHelper;
use AppGear\CoreBundle\Model\ModelManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PropertyAccess\PropertyAccessor;
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
     * @param Storage         $storage          Storage
     * @param ModelManager    $modelManager     Model manager
     * @param ViewManager     $viewManager      View manager
     * @param SecurityManager $securityManager  Security manager
     * @param FormBuilder     $formBuilder      Form builder for model
     * @param LoggerInterface $logger           Logger
     */
    public function __construct(
        Storage $storage,
        ModelManager $modelManager,
        ViewManager $viewManager,
        SecurityManager $securityManager,
        FormBuilder $formBuilder,
        FormManager $formManager,
        LoggerInterface $logger
    ) {
        parent::__construct($storage, $modelManager, $viewManager, $securityManager);

        $this->formBuilder      = $formBuilder;
        $this->logger           = $logger;
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
        $model  = $this->modelManager->get($model);
        $entity = $this->storage->getRepository($model)->find($id);

        $this->checkAccess((string) $model, $entity);

        $formBuilder = $this->formManager->getBuilder($model, $entity);
        $form        = $formBuilder->getForm();

        if ($this->formManager->submitRequest($formBuilder, $form, $request)) {
            $this->storage->getRepository($model)->save($entity);

            return $this->buildResponse($request, $model, $entity);
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
     * @param object $entity Entity
     */
    public function checkAccess($model, $entity)
    {
        if ($entity->getId() === null && !$this->securityManager->check(BasicPermissionMap::PERMISSION_CREATE, $model)) {
            throw new AccessDeniedHttpException();
        } elseif ($entity->getId() !== null && !$this->securityManager->check(BasicPermissionMap::PERMISSION_EDIT, $model)) {
            throw new AccessDeniedHttpException();
        }
    }

    /**
     * Build response
     *
     * @param Request $request Request
     * @param Model   $model   Entity model
     * @param object  $entity  Entity
     *
     * @return Response
     */
    protected function buildResponse(Request $request, Model $model, $entity)
    {
        if ($response = $this->buildRedirectResponse($request, $model, $entity)) {
            return $response;
        }
        if ($response = $this->buildSuccessResponse($request, $model, $entity)) {
            return $response;
        }

        return new Response();
    }
}