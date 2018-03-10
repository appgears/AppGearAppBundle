<?php

namespace AppGear\AppBundle\Controller;

use AppGear\AppBundle\Form\FormBuilder;
use AppGear\AppBundle\Form\FormManager;
use AppGear\AppBundle\Security\SecurityManager;
use AppGear\AppBundle\Storage\Storage;
use AppGear\AppBundle\View\ViewManager;
use AppGear\CoreBundle\Model\ModelManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Acl\Permission\BasicPermissionMap;
use Twig_Environment;

class LiveEditController
{
    /**
     * @var Twig_Environment
     */
    private $twig;
    /**
     * @var Storage
     */
    private $storage;
    /**
     * @var ModelManager
     */
    private $modelManager;
    /**
     * @var SecurityManager
     */
    private $securityManager;
    /**
     * @var FormManager
     */
    private $formManager;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CrudController constructor.
     *
     * @param Twig_Environment $twig            Twig
     * @param Storage          $storage         Storage
     * @param ModelManager     $modelManager    Model manager
     * @param SecurityManager  $securityManager Security manager
     * @param FormManager      $formManager     Form builder for model
     * @param LoggerInterface  $logger          Logger
     */
    public function __construct(
        Twig_Environment $twig,
        Storage $storage,
        ModelManager $modelManager,
        SecurityManager $securityManager,
        FormManager $formManager,
        LoggerInterface $logger
    ) {
        $this->twig            = $twig;
        $this->storage         = $storage;
        $this->modelManager    = $modelManager;
        $this->securityManager = $securityManager;
        $this->formManager     = $formManager;
        $this->logger          = $logger;
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
    public function formAction(Request $request, string $model, string $id, array $properties)
    {
        $model  = $this->modelManager->get($model);
        $entity = $this->storage->getRepository($model)->find($id);

        $this->checkAccess((string) $model, $entity);

        $formBuilder = $this->formManager->getBuilder($model, $entity, $properties);
        $form        = $formBuilder->getForm();

        if ($this->formManager->submit($formBuilder, $form, $request, $model)) {
            $this->storage->getRepository($model)->save($entity);

            return $this->buildResponse($request, $model, $entity);
        }

        if (!$form->isValid()) {
            $this->logger->error((string) $form->getErrors(true));
        }

        return $this->twig->render('{{ form(form) }}', ['form' => $form->createView()]);
    }

    /**
     * Check access
     *
     * @param string $model
     * @param object $entity Entity
     */
    public function checkAccess($model, $entity)
    {
        // TODO: temp
        return;

        if ($entity->getId() === null && !$this->securityManager->check(BasicPermissionMap::PERMISSION_CREATE, $model)) {
            throw new AccessDeniedHttpException();
        } elseif ($entity->getId() !== null && !$this->securityManager->check(BasicPermissionMap::PERMISSION_EDIT, $model)) {
            throw new AccessDeniedHttpException();
        }
    }
}