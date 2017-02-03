<?php

namespace AppGear\AppBundle\Controller;

use AppGear\AppBundle\Form\FormBuilder;
use AppGear\AppBundle\Storage\Storage;
use AppGear\AppBundle\View\ViewManager;
use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Model\ModelManager;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class FormController extends AbstractController
{
    /**
     * Model form builder
     *
     * @var FormBuilder
     */
    protected $formBuilder;

    /**
     * CrudController constructor.
     *
     * @param Storage      $storage      Storage
     * @param ModelManager $modelManager Model manager
     * @param ViewManager  $viewManager  View manager
     * @param FormBuilder  $formBuilder  Form builder for model
     */
    public function __construct(
        Storage $storage,
        ModelManager $modelManager,
        ViewManager $viewManager,
        FormBuilder $formBuilder
    )
    {
        parent::__construct($storage, $modelManager, $viewManager);

        $this->formBuilder = $formBuilder;
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
        $modelId = $this->requireAttribute($request, '_model');
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
        $viewParameters = $this->requireAttribute($request, '_view');
        $view           = $this->initialize($request, $viewParameters);

        // Инициализируем FormView
        // Если используется ContainerView, то FormView будет вложена в ContainerView
        $formViewPath = $request->attributes->get('_form_view_path');
        if ($formViewPath !== null) {
            $accessor = new PropertyAccessor();
            $formView = $accessor->getValue($view, $formViewPath);
            $formView->setForm($form);
        } else {
            // Иначе текущее отображение и есть FormView
            $view->setForm($form);
        }

        return $this->viewResponse($view);
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

            return $form->isSubmitted() && $form->isValid();
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