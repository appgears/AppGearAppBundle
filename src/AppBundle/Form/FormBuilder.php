<?php

namespace AppGear\AppBundle\Form;

use AppGear\AppBundle\Form\Transformer\ChoicesCollectionToValuesTransformer;
use AppGear\AppBundle\Storage\Storage;
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
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\PropertyAccess\PropertyAccessor;

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
     * Upload directory
     *
     * @var string
     */
    private $uploadDirectory;

    /**
     * Prefix for uploaded files
     *
     * @var string
     */
    private $uploadFilePrefix;

    /**
     * FormBuilder constructor.
     *
     * @param FormFactoryInterface $formFactory      Form factory
     * @param ModelManager         $modelManager     Model manager
     * @param TaggedManager        $taggedManager    Tagged services manager
     * @param Storage              $storage          Storage
     * @param string               $uploadDirectory  Upload directory
     * @param string               $uploadFilePrefix Prefix for uploaded files
     */
    public function __construct(FormFactoryInterface $formFactory,
                                ModelManager $modelManager,
                                TaggedManager $taggedManager,
                                Storage $storage,
                                string $uploadDirectory,
                                string $uploadFilePrefix)
    {
        $this->formFactory      = $formFactory;
        $this->modelManager     = $modelManager;
        $this->taggedManager    = $taggedManager;
        $this->storage          = $storage;
        $this->uploadDirectory  = $uploadDirectory;
        $this->uploadFilePrefix = $uploadFilePrefix;
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
     * @param array                $allowedProperties [Optional] Add form fields only for passed properties
     *
     * @return FormBuilderInterface
     */
    public function build(FormBuilderInterface $formBuilder, Model $model, array $allowedProperties = [])
    {
        foreach (ModelHelper::getProperties($model) as $property) {
            if ($allowedProperties !== [] && !isset($allowedProperties[$property->getName()])) {
                continue;
            }

            $allowedSubProperties = $allowedProperties[$property->getName()] ?? [];

            $this->addProperty($formBuilder, $property, $allowedSubProperties);
        }

        return $formBuilder;
    }

    /**
     * Add model property as field to form builder
     *
     * @param FormBuilderInterface $formBuilder       Form builder
     * @param Property             $property          Property
     * @param array                $allowedProperties Allowed properties (for composition form)
     */
    public function addProperty(FormBuilderInterface $formBuilder, Property $property, array $allowedProperties = [])
    {
        $propertyName = $property->getName();

        if ($property instanceof Field) {
            list($type, $options) = $this->resolveFieldType($property);
            $options['required'] = false;

            $formBuilder->add($propertyName, $type, $options);

        } elseif ($property instanceof Relationship) {
            /** @var Model $target */
            $target = $property->getTarget();

            if (!$property->getComposition()) {

                // TODO: переделать на choices
                $choiceLoader = new ModelChoiceLoader($this->storage, $target);
                $options      = [
                    'choice_loader' => $choiceLoader,
                    'required'      => false
                ];

                if ($property instanceof Relationship\ToMany) {
                    $options['multiple'] = true;
                }

                $formBuilder->add($propertyName, ChoiceType::class, $options);

                // Add special transformer to toMany associations
                // because, toMany properties contains PersistentCollection, but ChoiceType supports only array
                if (isset($options['multiple'])) {
                    $formBuilder
                        ->get($propertyName)
                        ->addModelTransformer(
                            new ChoicesCollectionToValuesTransformer(new LazyChoiceList($choiceLoader))
                        );
                }
            } else {
                $targetFqcn = $this->modelManager->fullClassName($target);

                if ($property instanceof Relationship\ToMany) {

                    // TODO: использовать FormBuilder вместо RelatedDynamicType
                    $formBuilder->add(
                        $propertyName,
                        CollectionType::class,
                        [
                            'entry_type'     => new RelatedDynamicType($this, $property, $this->modelManager),
                            'allow_add'      => true,
                            'prototype_data' => new $targetFqcn,
                            'options'        => ['label' => false] // Removing indexes (labels) for collection items
                        ]
                    );
                } elseif ($property instanceof Relationship\ToOne) {

                    $subFormBuilder = $this->formFactory->createNamedBuilder($propertyName, 'form', null, ['data_class' => $targetFqcn ]);
                    $subFormBuilder = $this->build($subFormBuilder, $target, $allowedProperties);

                    $formBuilder->add($subFormBuilder);
                }
            }
        }
    }

    /**
     * When creating a form to edit an already persisted item, the file form type still expects a  File instance.
     * As the persisted entity now contains only the relative file path, you first have to concatenate the configured
     * upload path with the stored filename and create a new File class.
     *
     * @param FormBuilderInterface $formBuilder
     * @param object               $entity
     */
    private function initFileField(FormBuilderInterface $formBuilder, $entity)
    {
        $accessor = new PropertyAccessor();

        /** @var FormBuilderInterface $field */
        foreach ($formBuilder as $field) {
            if ($field->getType()->getName() === 'file') {
                $fieldName = $field->getName();

                $file = $accessor->getValue($entity, $fieldName);
                if (!is_string($file)) {
                    continue;
                }

                // Avoid erasing field value when form will saved without new file
                $this->existingFileFields[$fieldName] = $file;

                $file = new File($this->uploadDirectory . str_replace($this->uploadFilePrefix, '', $file));

                $accessor->setValue($entity, $fieldName, $file);
            }
        }
    }

    /**
     * Resolve form field type for model field
     *
     * @param Field $field Model field
     *
     * @return mixed
     */
    private function resolveFieldType(Field $field)
    {
        $fieldModel = $this->modelManager->getByInstance($field);

        /** @var FormFieldTypeServiceInterface $service */
        if ($service = $this->taggedManager->get('form.property.field.service', ['field' => $fieldModel->getName()])) {
            return [$service->getFormType(), $service->getFormOptions()];
        }

        return [TextType::class, []];
    }
}