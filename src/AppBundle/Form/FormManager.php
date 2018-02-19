<?php

namespace AppGear\AppBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class FormManager
{
    /**
     * Form factory
     *
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * FormManager constructor.
     *
     * @param FormFactoryInterface $formFactory Form factory
     */
    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function getBuilder(Model $model = null, $entity = null)
    {
        $builder = $this->formFactory->createBuilder('form', $data);

        $this->initFields($builder, $model);
        $this->initFiles($builder);


        // TODO: fix
        $formBuilder->add('save', SubmitType::class, array('label' => 'Save'));
    }

    private function initFields(FormBuilderInterface $builder, Model $model)
    {
        foreach (ModelHelper::getProperties($model) as $property) {
            $this->addProperty($formBuilder, $property);
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
    private function initFiles(FormBuilderInterface $formBuilder, $entity)
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

    public function submit(FormBuilderInterface $builder, FormInterface $form, Request $request)
    {
        $data = $request->getData();

        $form->submit($data);

        if (!$form->isValid()) {
            return false;
        }

        $this->uploadFiles($formBuilder);
        $this->updateMappedRelationshipForCollection($formBuilder, $model);

        return true;
    }
}