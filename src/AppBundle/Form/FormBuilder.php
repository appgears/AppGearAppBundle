<?php

namespace AppGear\AppBundle\Form;

use AppGear\AppBundle\Storage\Storage;
use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Entity\Property\Field;
use AppGear\CoreBundle\Entity\Property\Relationship;
use AppGear\CoreBundle\EntityService\ModelService;
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
     * FormBuilder constructor.
     *
     * @param FormFactoryInterface $formFactory Form factory
     */
    public function __construct(FormFactoryInterface $formFactory, Storage $storage)
    {
        $this->formFactory = $formFactory;
        $this->storage     = $storage;
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
                $form->add($propertyName, TextType::class, [
                    'required' => false
                ]);
            } elseif ($property instanceof Relationship) {
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
}