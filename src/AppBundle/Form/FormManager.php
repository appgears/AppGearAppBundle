<?php

namespace AppGear\AppBundle\Form;

use AppGear\AppBundle\Form\FormBuilder as AppFormBuilder;
use AppGear\CoreBundle\Entity\Model;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class FormManager
{
    /**
     * @var FormBuilder
     */
    private $appFormBuilder;

    /**
     * FormManager constructor.
     *
     * @param FormBuilder $appFormBuilder
     */
    public function __construct(AppFormBuilder $appFormBuilder)
    {
        $this->appFormBuilder = $appFormBuilder;
    }

    /**
     * @param Model $model
     * @param null  $entity
     * @param array $properties
     * @param array $formOptions
     *
     * @return FormBuilderInterface
     */
    public function getBuilder(Model $model, $entity = null, array $properties = [], array $formOptions = [])
    {
        $builder = $this->appFormBuilder->create($entity, $formOptions);
        $builder = $this->appFormBuilder->build($builder, $model, $properties);

        return $builder;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param FormInterface        $form
     * @param Request              $request
     *
     * @return bool
     */ 
    public function submit(FormBuilderInterface $builder, FormInterface $form, Request $request)
    {
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return false;
        }

        if (!$form->isValid()) {
            return false;
        }

        $this->uploadFiles($builder);
        $this->updateMappedRelationshipForCollection($builder, $model);

        return true;
    }

    /**
     * @param FormBuilderInterface $formBuilder
     */
    private function uploadFiles(FormBuilderInterface $formBuilder)
    {
        $data     = $formBuilder->getData();
        $accessor = new PropertyAccessor();

        /** @var FormBuilderInterface $field */
        foreach ($formBuilder as $field) {
            if ($field->getType()->getName() === 'file') {
                $fieldName = $field->getName();

                /** @var UploadedFile $file */
                $file = $accessor->getValue($data, $fieldName);
                if (!($file instanceof UploadedFile)) {

                    // Avoid erasing field value when form will saved without new file
                    if (isset($this->existingFileFields[$fieldName])) {
                        $accessor->setValue($data, $fieldName, $this->existingFileFields[$fieldName]);
                    }

                    continue;
                }

                $fileName = $this->uploadFilePrefix . pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.' . $file->guessExtension();
                $file->move($this->uploadDirectory, $fileName);

                $accessor->setValue($data, $fieldName, $fileName);
            }
        }
    }

    /**
     * Set relationship from related backside
     *
     * @param FormBuilderInterface $formBuilder Form builder
     * @param Model                $model       Model
     */
    private function updateMappedRelationshipForCollection(FormBuilderInterface $formBuilder, Model $model)
    {
        $data     = $formBuilder->getData();
        $accessor = new PropertyAccessor();

        /** @var FormBuilderInterface $field */
        foreach ($formBuilder as $field) {
            if ($field->getType()->getName() === 'collection') {
                $property = ModelHelper::getRelationship($model, $field->getName());
                if (null !== $backsideProperty = StorageHelper::getBacksideProperty($property)) {
                    $relatedData = $accessor->getValue($data, $property->getName());
                    foreach ($relatedData as $relatedItem) {
                        $accessor->setValue($relatedItem, $backsideProperty->getName(), $data);
                    }
                }
            }
        }
    }
}