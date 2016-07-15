<?php

namespace AppGear\AppBundle\Form;

use AppGear\AppBundle\Storage\Storage;
use AppGear\CoreBundle\DependencyInjection\TaggedManager;
use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Entity\Property\Field;
use AppGear\CoreBundle\Entity\Property\Relationship;
use AppGear\CoreBundle\EntityService\ModelService;
use AppGear\CoreBundle\Model\ModelManager;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
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
     * Build form for model
     *
     * @param Model  $model  Model
     * @param object $entity Model entity
     *
     * @return Form
     */
    public function build(Model $model, $entity = null)
    {
        $modelService = new ModelService($model);
        $form         = $this->formFactory->createBuilder('form', $entity);
        foreach ($modelService->getAllProperties() as $property) {
            $propertyName = $property->getName();

            if ($property instanceof Field) {
                $type = $this->resolveFieldType($property);
                $form->add($propertyName, $type, [
                    'required' => false
                ]);
            } elseif ($property instanceof Relationship) {

                // Temporary don't support
                if ($property instanceof Relationship\ToMany) {
                    continue;
                }

                $options = [
                    'choice_loader' => new ModelChoiceLoader($this->storage, $property->getTarget()),
                    'required' => false
                ];
                if ($property instanceof Relationship\ToMany) {
                    $options['multiple'] = true;
                }

                $form->add($propertyName, ChoiceType::class, $options);
            }
        }
        $form->add('save', SubmitType::class, array('label' => 'Save'));

        return $form->getForm();
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
            return $service->getFormType();
        }

        return TextType::class;
    }
}