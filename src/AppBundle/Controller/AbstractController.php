<?php

namespace AppGear\AppBundle\Controller;

use AppGear\AppBundle\Entity\View;
use AppGear\AppBundle\Storage\Storage;
use AppGear\AppBundle\View\ViewManager;
use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Entity\Property\Collection;
use AppGear\CoreBundle\Entity\Property\Field;
use AppGear\CoreBundle\Entity\Property\Relationship;
use AppGear\CoreBundle\EntityService\ModelService;
use AppGear\CoreBundle\Model\ModelManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class AbstractController extends Controller
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
    protected $modelManager;

    /**
     * View manager
     *
     * @var ViewManager
     */
    protected $viewManager;

    /**
     * CrudController constructor.
     *
     * @param Storage      $storage      Storage
     * @param ModelManager $modelManager Model manager
     * @param ViewManager  $viewManager  View manager
     */
    public function __construct(
        Storage $storage,
        ModelManager $modelManager,
        ViewManager $viewManager
    ) {
        $this->storage      = $storage;
        $this->modelManager = $modelManager;
        $this->viewManager  = $viewManager;
    }

    /**
     * Try to get attribute from request
     *
     * @param Request $request   Request
     * @param string  $attribute Required attribute
     *
     * @return string Attribute value
     */
    protected function requireAttribute(Request $request, $attribute)
    {
        if ($value = $request->attributes->get($attribute)) {
            return $value;
        }

        throw new BadRequestHttpException(sprintf('Expect %s parameter', $attribute));
    }

    /**
     * Perform redirect if redirect is configured
     *
     * @param Request $request Request
     *
     * @return null|RedirectResponse
     */
    protected function buildRedirectResponse(Request $request)
    {
        if (!$redirect = $request->attributes->get('_success[_redirect]', null, true)) {
            return null;
        }

        if (is_string($redirect)) {
            return $this->redirectToRoute($redirect);
        } elseif (is_array($redirect) && array_key_exists('name', $redirect)) {
            $route      = $redirect['name'];
            $parameters = array_key_exists('parameters', $redirect) ? $redirect['parameters'] : [];
            $parameters = array_map(
                function ($parameter) use ($request) {
                    return $this->performExpression($request, $parameter);
                },
                $parameters
            );

            return $this->redirectToRoute($route, $parameters);
        }
    }

    /**
     * Build view response for successfully action
     *
     * @param Request $request Request
     * @param object  $entity  Entity
     *
     * @return null|Response
     */
    protected function buildSuccessResponse(Request $request, $entity)
    {
        if (!$viewParameters = $request->attributes->get('_success[_view]', null, true)) {
            return null;
        }

        $view = $this->initialize($request, $viewParameters);
        $view->setEntity($entity);

        return new Response($this->viewManager->getViewService($view)->render());
    }

    /**
     * Try to get value from request
     *
     * @param Request $request Request
     * @param string  $name    Parameter name
     *
     * @return mixed
     */
    protected function getFromRequest(Request $request, $name)
    {
        if ($request->query->has($name)) {
            $value = $request->query->get($name);
        } elseif ($request->request->has($name)) {
            $value = $request->request->get($name);
        } elseif ($request->attributes->has($name)) {
            $value = $request->attributes->get($name);
        } elseif ($request->cookies->has($name)) {
            $value = $request->cookies->get($name);
        } else {
            throw new BadRequestHttpException(sprintf('Undefined parameter link: "%s"', $name));
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
    protected function performExpression(Request $request, $expression)
    {
        if (preg_match_all('/\{(.+?)\}/', $expression, $matches)) {
            if (isset($matches[1])) {

                // If expression contains only parameter - then return this parameter (prevent cast to string)
                if ((count($matches[1]) === 1) && ($expression[0] === '{') && ($expression[strlen($expression) - 1] === '}')) {
                    return $this->getFromRequest($request, $matches[1][0]);
                }

                foreach ($matches[1] as $match) {
                    $value      = $this->getFromRequest($request, $match);
                    if ($value === null) {
                        $value = 'null';
                    }
                    $expression = str_replace('{' . $match . '}', $value, $expression);
                }
            }
        }

        return $expression;
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
            $id = $this->performExpression($request, $id);

            // TODO: надо переписать после интеграции моделей с хранилищем
            if ($modelId === 'core.model') {
                $instance = $this->modelManager->get($id);
            } else {
                $instance = $this->storage->getRepository($modelId)->find($id);
            }

            if ($instance === null) {
                throw new NotFoundHttpException;
            }
        } elseif ($expr !== null) {
            $expr     = $this->performExpression($request, $expr);
            $instance = $this->storage->getRepository($modelId)->findOneByExpr($expr);
            if ($instance === null) {
                throw new NotFoundHttpException;
            }
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
                    $value = $this->performExpression($request, $value);
                } elseif ($property instanceof Relationship\ToOne) {
                    $value = $this->initialize($request, $propertyParameters, $property->getTarget());
                } elseif ($property instanceof Relationship\ToMany) {
                    $value = [];
                    foreach ($propertyParameters as $subParameters) {
                        if (is_scalar($subParameters)) {
                            $subParameters = ['_id' => $subParameters];
                        }
                        $value[] = $this->initialize($request, $subParameters, $property->getTarget());
                    }
                } elseif ($property instanceof Collection) {
                    $propertyModel = $propertyParameters['_model'];
                    $propertyModel = $this->performExpression($request, $propertyModel);

                    $orderings = [];
                    if (array_key_exists('_orderings', $propertyParameters)) {
                        $orderings = $propertyParameters['_orderings'];
                    }

                    if (array_key_exists('_expression', $propertyParameters)) {
                        $expr = $propertyParameters['_expression'];
                        $expr = $this->performExpression($request, $expr);

                        $value = $this->storage->getRepository($propertyModel)->findByExpr($expr, $orderings);
                    } elseif ($orderings !== []) {
                        $value = $this->storage->getRepository($propertyModel)->findBy([], $orderings);
                    } else {
                        $value = $this->storage->getRepository($propertyModel)->findAll();
                    }
                }

                $instance->{'set' . ucfirst($propertyName)}($value);
            }
        }

        return $instance;
    }

    /**
     * Generate response for view
     *
     * @param View $view View
     *
     * @return Response
     */
    protected function viewResponse(View $view)
    {
        $response = new Response($this->viewManager->getViewService($view)->render());
        
        if ($view->getUserSpecifiedContent() === false) {
            $response->setPublic();
            $response->setEtag(md5($response->getContent()));
        } elseif ($view->getUserSpecifiedContent() === true) {
            $response->setPrivate();
        }

        return $response;
    }
}