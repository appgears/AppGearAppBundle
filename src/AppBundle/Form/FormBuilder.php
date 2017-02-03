<?php

namespace AppGear\AppBundle\Form;

use AppGear\AppBundle\Entity\Storage\Column;
use AppGear\AppBundle\Form\Transformer\ChoicesCollectionToValuesTransformer;
use AppGear\AppBundle\Storage\Storage;
use AppGear\CoreBundle\DependencyInjection\TaggedManager;
use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Entity\Property;
use AppGear\CoreBundle\Entity\Property\Field;
use AppGear\CoreBundle\Entity\Property\Relationship;
use AppGear\CoreBundle\EntityService\ModelService;
use AppGear\CoreBundle\Model\ModelManager;
use Symfony\Component\Form\ChoiceList\LazyChoiceList;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
    public function __construct(FormFactoryInterface $formFactory,
                                ModelManager $modelManager,
                                TaggedManager $taggedManager,
                                Storage $storage)
    {
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
     * @param FormBuilderInterface $formBuilder Form builder
     * @param Model                $model       Model
     *
     * @return FormBuilderInterface
     */
    public function build(FormBuilderInterface $formBuilder, Model $model)
    {
        $modelService = new ModelService($model);
        foreach ($modelService->getAllProperties() as $property) {
            $this->addProperty($formBuilder, $property);
        }

        $formBuilder->add('save', SubmitType::class, array('label' => 'Save'));

        return $formBuilder;
    }

    /**
     * Add model property as field to form builder
     *
     * @param FormBuilderInterface $formBuilder Form builder
     * @param Property             $property    Property
     */
    public function addProperty(FormBuilderInterface $formBuilder, Property $property)
    {
        $propertyName = $property->getName();

        if ($property instanceof Field) {
            list($type, $options) = $this->resolveFieldType($property);
            $options['required'] = false;
            $formBuilder->add($propertyName, $type, $options);
        } elseif ($property instanceof Relationship) {
            $compositionRelation = false;
            foreach ($property->getExtensions() as $extension) {
                if ($extension instanceof Column) {
                    $compositionRelation = $extension->getComposition();
                }
            }

            if (!$compositionRelation) {
                $choiceLoader = new ModelChoiceLoader($this->storage, $property->getTarget());
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
                $formBuilder->add(
                    $propertyName,
                    CollectionType::class,
                    [
                        'entry_type' => new RelatedDynamicType($this, $property),
                        'allow_add'  => true,
                        'options'    => ['label' => false] // Removing indexes (labels) for collection items
                    ]
                );
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