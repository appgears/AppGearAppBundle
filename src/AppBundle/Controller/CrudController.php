<?php

namespace AppGear\AppBundle\Controller;

use AppGear\AppBundle\Form\ModelChoiceLoader;
use AppGear\AppBundle\Storage\Storage;
use AppGear\AppBundle\View\ViewManager;
use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Entity\Property\Collection;
use AppGear\CoreBundle\Entity\Property\Field;
use AppGear\CoreBundle\Entity\Property\Relationship;
use AppGear\CoreBundle\EntityService\ModelService;
use AppGear\CoreBundle\Model\ModelManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class CrudController extends Controller
{
    /**
     * Storage
     *
     * @var Storage
     */
    protected $storage;

    /**
     * Model
     *
     * @var ModelManager
     */
    private $modelManager;

    /**
     * View manager
     *
     * @var ViewManager
     */
    private $viewManager;

    /**
     * CrudController constructor.
     *
     * @param Storage      $storage      Storage
     * @param ModelManager $modelManager Model manager
     * @param ViewManager  $viewManager  View manager
     */
    public function __construct(Storage $storage, ModelManager $modelManager, ViewManager $viewManager)
    {
        $this->storage      = $storage;
        $this->modelManager = $modelManager;
        $this->viewManager  = $viewManager;
    }

    /**
     * Try to get _model attribute from request
     *
     * @param Request $request   Request
     * @param string  $attribute Required attribute
     *
     * @return string Attribute value
     */
    protected function requireAttribute(Request $request, $attribute)
    {
        if ($model = $request->attributes->get($attribute)) {
            return $model;
        }

        throw new BadRequestHttpException(sprintf('Expects %s parameter', $attribute));
    }

    /**
     * Action for view
     *
     * @param Request $request
     *
     * @return Response
     */
    public function viewAction(Request $request)
    {
        $viewParameters = $this->requireAttribute($request, '_view');
        $view           = $this->initialize($request, $viewParameters);

        return new Response($this->viewManager->getViewService($view)->render());
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
        $formViewPath = $this->requireAttribute($request, '_form_view_path');

        $formModelId       = $this->requireAttribute($request, '_model');
        $formModel         = $this->modelManager->get($formModelId);
        $storageRepository = $this->storage->getRepository($formModel);

        if ($id === null) {
            $entity = $this->modelManager->instance($formModelId);
            $entity = $this->modelManager->injectServices($formModelId, $entity);
        } else {
            $entity = $storageRepository->find($id);
        }
        $form = $this->buildForm($formModel, $entity);

        $viewParameters = $this->requireAttribute($request, '_view');
        $view           = $this->initialize($request, $viewParameters);

        $accessor = new PropertyAccessor();
        $formView = $accessor->getValue($view, $formViewPath);
        $formView->setForm($form);


        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $storageRepository->save($entity);

                return new Response('Save!');
            }
        }

        return new Response($this->viewManager->getViewService($view)->render());
    }

    /**
     * Build form for model
     *
     * @param Model  $model  Model
     * @param object $entity Model entity
     *
     * @return Form
     */
    protected function buildForm(Model $model, $entity = null)
    {
        $form = $this->createFormBuilder($entity);
        foreach ($model->getProperties() as $property) {
            if ($property instanceof Field) {
                $form->add($property->getName(), TextType::class);
            } elseif ($property instanceof Relationship) {
                $form->add($property->getName(), ChoiceType::class, [
                    'choice_loader' => new ModelChoiceLoader($this->storage, $property->getTarget())
                ]);
            }
        }
        $form->add('save', SubmitType::class, array('label' => 'Save'));

        return $form->getForm();
    }

    /**
     * Initialize object
     *
     * @param Request    $request       Request object
     * @param array      $parameters    Object parameters
     * @param Model|null $possibleModel Possible model for object
     *                                  If parameters does not contain the "_model" parameter
     *
     * @return object
     */
    public function initialize(Request $request, array $parameters, Model $possibleModel = null)
    {
        // Get model ID from parameters and load model
        if (array_key_exists('_model', $parameters)) {
            $modelId = $parameters['_model'];
            $model   = $this->modelManager->get($modelId);
        } elseif ($possibleModel !== null) {
            $modelId = $possibleModel->getName();
            $model   = $possibleModel;
        } else {
            throw new BadRequestHttpException(sprintf('Can not determine the model for parameters section: %s',
                var_export($parameters, true)));
        }

        // Get ID or expression from parameters
        $id   = array_key_exists('_id', $parameters) ? $parameters['_id'] : null;
        $expr = array_key_exists('_expression', $parameters) ? $parameters['_expression'] : null;

        // Load instance if ID passed
        if ($id !== null) {
            $id       = $this->performLink($request, $id);
            $instance = $this->storage->getRepository($modelId)->find($id);
        } elseif ($expr !== null) {
            $expr       = $this->performEmbeddedLink($request, $expr);
            $instance = $this->storage->getRepository($modelId)->findOneByExpr($expr);
        } else {
            // Else create new instance
            $instance = $this->modelManager->instance($modelId);
        }

        $properties = (new ModelService($model))->getAllProperties();
        foreach ($properties as $property) {
            $propertyName = $property->getName();
            if (array_key_exists($propertyName, $parameters)) {
                $value              = null;
                $propertyParameters = $parameters[$propertyName];

                if ($property instanceof Field) {
                    $value = $propertyParameters;
                    $value = $this->performLink($request, $value);
                } elseif ($property instanceof Relationship\ToOne) {
                    $value = $this->initialize($request, $propertyParameters, $property->getTarget());
                } elseif ($property instanceof Relationship\ToMany) {
                    $value = [];
                    foreach ($propertyParameters as $subParameters) {
                        $value[] = $this->initialize($request, $subParameters, $property->getTarget());
                    }
                } elseif ($property instanceof Collection) {
                    if (array_key_exists('_expression', $propertyParameters)) {
                        $expr  = $propertyParameters['_expression'];
                        $expr  = $this->performEmbeddedLink($request, $expr);
                        $value = $this->storage->getRepository($propertyParameters['_model'])->findByExpr($expr);
                    } else {
                        $value = $this->storage->getRepository($propertyParameters['_model'])->findAll();
                    }
                }

                $setter = 'set' . ucfirst($propertyName);
                $instance->$setter($value);
            }
        }

        return $instance;
    }

    /**
     * If value is link - try to extract target value
     * Else, return the original value
     *
     * @param Request $request Request
     * @param string  $value   Value
     *
     * @return string
     */
    protected function performLink(Request $request, $value)
    {
        if (strlen($value) > 2 && $value[0] === '{' && $value[strlen($value) - 1] === '}') {
            $link = substr($value, 1, -1);
            if ($request->query->has($link)) {
                $value = $request->query->get($link);
            } elseif ($request->request->has($link)) {
                $value = $request->request->get($link);
            } elseif ($request->attributes->has($link)) {
                $value = $request->attributes->get($link);
            } else {
                throw new BadRequestHttpException(sprintf('Undefined parameter link: "%s"', $link));
            }
        }

        return $value;
    }

    /**
     * if expression contains links - process links and spoof it in the expression
     *
     * @param Request $request    Request
     * @param string  $expression Expression
     *
     * @return string
     */
    protected function performEmbeddedLink(Request $request, $expression)
    {
        if (preg_match_all('/\{.+?\}/', $expression, $matches)) {
            foreach ($matches as $match) {
                $link       = current($match);
                $value      = $this->performLink($request, $link);
                $expression = str_replace($link, $value, $expression);
            }
        }

        return $expression;
    }
}