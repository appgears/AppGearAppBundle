<?php

namespace AppGear\AppBundle\Controller;

use AppGear\AppBundle\Entity\View;
use AppGear\AppBundle\Form\FormBuilder;
use AppGear\AppBundle\Form\FormManager;
use AppGear\AppBundle\Security\SecurityManager;
use AppGear\AppBundle\Storage\Storage;
use AppGear\AppBundle\View\ViewManager;
use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Entity\Property;
use AppGear\CoreBundle\Helper\ModelHelper;
use AppGear\CoreBundle\Helper\PropertyHelper;
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

        $this->checkAccess((string) $model, $id);

        // Пре-инициализация полей
        // Используется для прокидывания значений полей по-умолчанию
        // todo: Эти поля не выводятся на форме, но можно сделать опцию которая будет управлять этим поведением
        // todo: добавить namespace к параметрам [exclude]=name1&[exclude]=name2
        $initializedProperties = $request->query->keys();

        $this->formManager->build($model, $entity, [], $initializedProperties)->getSymfonyFormBuilder();
        $submitResult = $this->formManager->submit($request, $model);

        if ($submitResult->isSubmitted && $submitResult->isValid) {

            // Сейчас преинициализированные поля не выводятся, значения записываются в них на текущем этапе
            foreach ($request->query->all() as $initializedPropertyName => $initializedPropertyValue) {
                if (null !== $initializedProperty = ModelHelper::getProperty($model, $initializedPropertyName)) {
                    $this->setPropertyValue($entity, $model, $initializedProperty, $initializedPropertyValue);
                }
            }

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

    /**
     * Sets object property value.
     * Supports relationships - can get reference from scalar value and use it.
     *
     * @todo move to StorageHelper or Yaml driver
     *
     * @param          $object
     * @param Model    $model
     * @param Property $property
     * @param          $value
     */
    private function setPropertyValue($object, Model $model, Property $property, $value)
    {
        if (PropertyHelper::isRelationshipToOne($property)) {
            /** @var Property\Relationship\ToOne $property */
            $initializedReference = $this->storage->find($property->getTarget(), $value);
            ModelHelper::setPropertyValue($object, $property, $initializedReference);
        } elseif (PropertyHelper::isScalar($property)) {
            ModelHelper::setPropertyValue($object, $property, $value);
        } else {
            // todo: implement - остается вариант с toMany
        }
    }
}