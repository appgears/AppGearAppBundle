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
     * @var string
     */
    protected $uploadDirectory;

    /**
     * @var string
     */
    protected $uploadFilePrefix;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $existingFileFields = [];

    /**
     * CrudController constructor.
     *
     * @param Storage         $storage          Storage
     * @param ModelManager    $modelManager     Model manager
     * @param ViewManager     $viewManager      View manager
     * @param SecurityManager $securityManager  Security manager
     * @param FormBuilder     $formBuilder      Form builder for model
     * @param string          $uploadDirectory  Upload directory
     * @param string          $uploadFilePrefix Prefix for uploaded files
     * @param LoggerInterface $logger           Logger
     */
    public function __construct(
        Storage $storage,
        ModelManager $modelManager,
        ViewManager $viewManager,
        SecurityManager $securityManager,
        FormBuilder $formBuilder,
        FormManager $formManager,
        string $uploadDirectory,
        string $uploadFilePrefix,
        LoggerInterface $logger
    )
    {
        parent::__construct($storage, $modelManager, $viewManager, $securityManager);

        $this->formBuilder      = $formBuilder;
        $this->uploadDirectory  = $uploadDirectory;
        $this->uploadFilePrefix = $uploadFilePrefix;
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
        $model = $this->modelManager->get($model);

        // Загружаем существующую сущность или создаем новую
        // TODO: проверить работу с data_class и entity=null
        $entity = $this->loadEntity($model, $id);

        // Проверяем доступ
        $this->checkAccess((string)$model, $entity);

        // Собираем форму
        $formBuilder = $this->formManager->getBuilder($model, $entity);
        $form        = $formBuilder->getForm();

        if ($this->formManager->submit($formBuilder, $form, $request)) {
            $this->saveEntity($model, $entity);

            return $this->buildResponse($request, $model, $entity);
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
     * Load entity from the model storage by ID or create new entity instance
     *
     * @param Model $model Entity model
     * @param null  $id    Entity ID
     *
     * @return object
     */
    protected function loadEntity(Model $model, $id = null)
    {
        return ($id === null) ? $this->modelManager->instance($model->getName()) :
            $this->storage->getRepository($model)->find($id);
    }

    /**
     * Handle request with form
     *
     * @param Request       $request Request
     * @param FormInterface $form    Form
     *
     * @return bool True if form passed and successfully submitted
     */
    protected function submitForm(Request $request, FormInterface $form)
    {
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return false;
        }
        if (!$form->isValid()) {
            $this->logger->error((string)$form->getErrors(true));

            return false;
        }

        return true;
    }

    /**
     * @param FormBuilderInterface $formBuilder
     */
    protected function uploadFiles(FormBuilderInterface $formBuilder)
    {
        $data     = $formBuilder->getData();
        $accessor = new PropertyAccessor();

        /** @var FormBuilderInterface $field */
        foreach ($formBuilder as $field) {
            if ($field->getType()->getName() === 'file') {
                $fieldName = $field->getName();

                /** @var UploadedFile $file */
                $file = $accessor->getValue($data, $fieldName);
                if (!($file instanceof UploadedFile)) {

                    // Avoid erasing field value when form will saved without new file
                    if (isset($this->existingFileFields[$fieldName])) {
                        $accessor->setValue($data, $fieldName, $this->existingFileFields[$fieldName]);
                    }

                    continue;
                }

                $fileName = $this->uploadFilePrefix . pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.' . $file->guessExtension();
                $file->move($this->uploadDirectory, $fileName);

                $accessor->setValue($data, $fieldName, $fileName);
            }
        }
    }

    /**
     * Set relationship from related backside
     *
     * @param FormBuilderInterface $formBuilder Form builder
     * @param Model                $model       Model
     */
    protected function updateMappedRelationshipForCollection(FormBuilderInterface $formBuilder, Model $model)
    {
        $data     = $formBuilder->getData();
        $accessor = new PropertyAccessor();

        /** @var FormBuilderInterface $field */
        foreach ($formBuilder as $field) {
            if ($field->getType()->getName() === 'collection') {
                $property = ModelHelper::getRelationship($model, $field->getName());
                if (null !== $backsideProperty = StorageHelper::getBacksideProperty($property)) {
                    $relatedData = $accessor->getValue($data, $property->getName());
                    foreach ($relatedData as $relatedItem) {
                        $accessor->setValue($relatedItem, $backsideProperty->getName(), $data);
                    }
                }
            }
        }
    }

    /**
     * Save entity to storage
     *
     * @param Model  $model  Model
     * @param object $entity Entity
     */
    protected function saveEntity(Model $model, $entity)
    {
        $this->storage->getRepository($model)->save($entity);
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