<?php

namespace AppGear\AppBundle\Form;

use AppGear\AppBundle\Form\Transformer\ChoicesCollectionToValuesTransformer;
use AppGear\AppBundle\Helper\StorageHelper;
use AppGear\AppBundle\Storage\Storage;
use AppGear\CoreBundle\Collection\Collection;
use AppGear\CoreBundle\DependencyInjection\TaggedManager;
use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Entity\Property;
use AppGear\CoreBundle\Entity\Property\Field;
use AppGear\CoreBundle\Entity\Property\Relationship;
use AppGear\CoreBundle\Helper\ModelHelper;
use AppGear\CoreBundle\Model\ModelManager;
use Symfony\Component\Form\ChoiceList\LazyChoiceList;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;

class FormBuilder
{
    /**
     * Form factory
     *
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * Storage
     *
     * @var Storage
     */
    protected $storage;

    /**
     * Tagged manager
     *
     * @var TaggedManager
     */
    private $taggedManager;

    /**
     * Model manager
     *
     * @var ModelManager
     */
    private $modelManager;

    /**
     * FormBuilder constructor.
     *
     * @param FormFactoryInterface $formFactory   Form factory
     * @param ModelManager         $modelManager  Model manager
     * @param TaggedManager        $taggedManager Tagged services manager
     * @param Storage              $storage       Storage
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        ModelManager $modelManager,
        TaggedManager $taggedManager,
        Storage $storage
    ) {
        $this->formFactory   = $formFactory;
        $this->modelManager  = $modelManager;
        $this->taggedManager = $taggedManager;
        $this->storage       = $storage;
    }

    /**
     * Create symfony form builder
     *
     * @param object $entity  Model entity
     * @param array  $options Form builder options
     *
     * @return FormBuilderInterface
     */
    public function create($entity = null, array $options = [])
    {
        return $this->formFactory->createBuilder('form', $entity, $options);
    }

    /**
     * Build form
     *
     * @param FormBuilderInterface $formBuilder       Form builder
     * @param Model                $model             Model
     * @param array                $allowedProperties [Optional] Add form fields names only for passed properties
     * @param array                $excludeProperties [Optional] Skip passed fields
     *
     * @return FormBuilderInterface
     */
    public function buildByModel(FormBuilderInterface $formBuilder, Model $model, array $allowedProperties = [], array $excludeProperties = [])
    {
        $properties = Collection::create(ModelHelper::getProperties($model))
            ->filter([StorageHelper::class, 'isIdentifierProperty'])
            ->filter(function (Property $property) use ($excludeProperties) {
                return in_array($property->getName(), $excludeProperties);
            });

        return $this->buildByProperties($formBuilder, $properties, $allowedProperties);
    }

    /**
     * Build form by properties collection
     *
     * @param FormBuilderInterface $formBuilder
     * @param Collection           $propertiesCollection
     * @param array                $allowedProperties
     *
     * @return FormBuilderInterface
     */
    public function buildByProperties(FormBuilderInterface $formBuilder, Collection $propertiesCollection, array $allowedProperties = [], array $excludeProperties = [])
    {
        $propertiesCollection->filter([$this, 'isPropertyAllowed']);

        /** @var Property $property */
        foreach ($propertiesCollection->toArray() as $property) {
            $allowedSubProperties = $allowedProperties[$property->getName()] ?? [];
            $this->addProperty($formBuilder, $property, $allowedSubProperties);
        }

        return $formBuilder;
    }

    /**
     * Is property allowed (if allowed all properties (allowed is empty) or in allowed list)
     *
     * @param Property $property
     * @param array    $allowed
     *
     * @return bool
     */
    public function isPropertyAllowed(Property $property, array $allowed = [])
    {
        if ($allowed === [] || isset($allowedProperties[$property->getName()])) {
            return true;
        }

        return false;
    }

    /**
     * Add model property as field to form builder
     *
     * @param FormBuilderInterface $formBuilder            Form builder
     * @param Property             $property               Property
     * @param array                $allowedChildProperties Allowed properties (for composition form)
     * @param string               $name                   [Optional] Form field name, if empty then use property name
     *
     * @return FormBuilderInterface
     */
    public function addProperty(FormBuilderInterface $formBuilder, Property $property, array $allowedChildProperties = [], string $name = null, string $type = null)
    {
        if ($property->getCalculated() !== null) {
            return $formBuilder;
        }
        if ($property->getReadOnly()) {
            return $formBuilder;
        }

        $propertyName = $property->getName();
        $name         = $name ?? $propertyName;

        if ($property instanceof Field) {
            list($fieldType, $options) = $this->resolveFieldType($property);
            $type = $type ?? $fieldType;

            $formBuilder->add($name, $type, $options);
        } elseif ($property instanceof Relationship) {
            if (!$property->getComposition()) {

                list($relationType, $options) = $this->resolveRelationType($property);
                $type = $type ?? $relationType;

                $formBuilder->add($name, $type, $options);

                // Add special transformer to toMany associations
                // because, toMany properties contains PersistentCollection, but ChoiceType supports only array
                // https://github.com/symfony/symfony/issues/23192#issuecomment-308692855
                if (isset($options['multiple'])) {
                    $formBuilder
                        ->get($name)
                        ->addModelTransformer(
                            new ChoicesCollectionToValuesTransformer(new LazyChoiceList($options['choice_loader']))
                        );
                }
            } else {
                /** @var Model $target */
                $target     = $property->getTarget();
                $targetFqcn = $this->modelManager->fullClassName($target);

                if ($property instanceof Relationship\ToMany) {

                    // TODO: использовать FormBuilder вместо RelatedDynamicType
                    $formBuilder->add(
                        $name,
                        CollectionType::class,
                        [
                            'entry_type'     => new RelatedDynamicType($this, $property, $this->modelManager),
                            'allow_add'      => true,
                            'prototype_data' => new $targetFqcn,
                            'options'        => ['label' => false] // Removing indexes (labels) for collection items
                        ]
                    );
                } elseif ($property instanceof Relationship\ToOne) {

                    $subProperties = Collection::create(ModelHelper::getProperties($target))
                        ->filter([StorageHelper::class, 'isIdentifierProperty'])
                        ->filter([StorageHelper::class, 'isRelatedProperty'], [$property]);

                    $subFormBuilder = $this->formFactory->createNamedBuilder($propertyName, 'form', null, ['data_class' => $targetFqcn]);
                    $subFormBuilder = $this->buildByProperties($subFormBuilder, $subProperties, $allowedChildProperties);

                    $formBuilder->add($subFormBuilder);
                }
            }

            return $formBuilder;
        }
    }

    /**
     * Resolve form field type and options for model field
     *
     * @param Field $field Model field
     *
     * @return array
     */
    public function resolveFieldType(Field $field)
    {
        $fieldModel = $this->modelManager->getByInstance($field);

        $type    = TextType::class;
        $options = [];

        /** @var FormFieldTypeServiceInterface $service */
        if ($service = $this->taggedManager->get('form.property.field.service', ['field' => $fieldModel->getName()])) {
            $type    = $service->getFormType();
            $options = $service->getFormOptions();
        }

        // Define required options if does not set
        $options += ['required' => false];

        return [$type, $options];
    }

    /**
     * Resolve form field type and options for model relationship
     *
     * @param Relationship $relationship Model relationship
     *
     * @return array
     */
    public function resolveRelationType(Relationship $relationship)
    {
        $target = $relationship->getTarget();

        $choiceLoader = new ModelChoiceLoader($this->storage, $target);
        $options      = [
            'choice_loader' => $choiceLoader,
            'required'      => false
        ];

        if ($relationship instanceof Relationship\ToMany) {
            $options['multiple'] = true;
        }

        return [ChoiceType::class, $options];
    }
}