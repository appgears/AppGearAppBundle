<?php

namespace AppGear\AppBundle\Controller;

use AppGear\AppBundle\Form\FormBuilder;
use AppGear\AppBundle\Storage\Storage;
use AppGear\AppBundle\View\ViewManager;
use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Model\ModelManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FormController extends AbstractController
{
    /**
     * Model form builder
     *
     * @var FormBuilder
     */
    protected $formBuilder;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CrudController constructor.
     *
     * @param Storage         $storage      Storage
     * @param ModelManager    $modelManager Model manager
     * @param ViewManager     $viewManager  View manager
     * @param FormBuilder     $formBuilder  Form builder for model
     * @param LoggerInterface $logger       Logger
     */
    public function __construct(
        Storage $storage,
        ModelManager $modelManager,
        ViewManager $viewManager,
        FormBuilder $formBuilder,
        LoggerInterface $logger
    ) {
        parent::__construct($storage, $modelManager, $viewManager);

        $this->formBuilder = $formBuilder;
        $this->logger      = $logger;
    }

    /**
     * Action for form view and process
     *
     * @param Request $request Request
     * @param mixed   $id      Entity ID
     *
     * @return Response
     */
    public function formAction(Request $request, $id = null)
    {
        $modelId = $this->requireAttribute($request, 'model');
        $modelId = $this->performExpression($request, $modelId);
        $model   = $this->modelManager->get($modelId);

        // Загружаем существующую сущность или создаем новую
        $entity = $this->loadEntity($model, $id);

        // Собираем форму
        $form = $this->getForm($model, $entity);

        // Если форма была отправлена и успешно обработана
        if ($this->submitForm($request, $form)) {
            $this->saveEntity($model, $entity);

            if ($redirect = $this->buildRedirectResponse($request)) {
                return $redirect;
            } elseif ($response = $this->buildSuccessResponse($request, $entity)) {
                return $response;
            }

            return new Response();
        }

        // Инициализируем отображение
        $viewParameters = $this->requireAttribute($request, 'view');
        $view           = $this->initialize($request, $viewParameters);

        return $this->viewResponse($view, ['form' => $form->createView()]);
    }

    /**
     * Get form for model entity
     *
     * @param Model  $model  Model
     * @param object $entity Entity
     *
     * @return FormInterface
     */
    protected function getForm(Model $model, $entity)
    {
        return $this->formBuilder->build($this->formBuilder->create($entity), $model)->getForm();
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
     * @param Request $request Request
     * @param Form    $form    Form
     *
     * @return bool True if form passed and successfully submitted
     */
    protected function submitForm(Request $request, Form $form)
    {
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if (!$form->isSubmitted()) {
                return false;
            }
            if (!$form->isValid()) {
                $this->logger->error((string) $form->getErrors(true));

                return false;
            }

            return true;
        }

        return false;
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
}