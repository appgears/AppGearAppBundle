<?php

namespace AppGear\AppBundle\Form;

use AppGear\AppBundle\Form\Dto\FormSubmitResultDto;
use AppGear\AppBundle\Form\FormBuilder as AppFormBuilder;
use AppGear\AppBundle\Helper\StorageHelper;
use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Helper\ModelHelper;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\File\File;
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
     * @var FormBuilderInterface
     */
    private $symfonyFormBuilder;

    /**
     * @var FormInterface
     */
    private $form;

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
     * To avoid erasing field value when form will saved without new file
     *
     * @var array
     */
    private $fileFieldsSnapshot = [];

    /**
     * FormManager constructor.
     *
     * @param FormBuilder $appFormBuilder
     * @param string      $uploadDirectory  Upload directory
     * @param string      $uploadFilePrefix Prefix for uploaded files
     */
    public function __construct(AppFormBuilder $appFormBuilder, string $uploadDirectory, string $uploadFilePrefix)
    {
        $this->appFormBuilder   = $appFormBuilder;
        $this->uploadDirectory  = $uploadDirectory;
        $this->uploadFilePrefix = $uploadFilePrefix;
    }

    /**
     * Return AppGear form builder
     *
     * @return FormBuilder
     */
    public function getAppFormBuilder()
    {
        return $this->appFormBuilder;
    }

    /**
     * @return FormBuilderInterface
     */
    public function getSymfonyFormBuilder()
    {
        return $this->symfonyFormBuilder;
    }

    public function getForm()
    {
        if ($this->form === null) {
            $this->form = $this->symfonyFormBuilder->getForm();
        }

        return $this->form;
    }

    /**
     * @param Model $model
     * @param null  $entity
     * @param array $properties
     * @param array $formOptions
     *
     * @return $this
     */
    public function build(Model $model, $entity = null, array $properties = [], array $formOptions = [])
    {
        $this->form = null;

        $this->symfonyFormBuilder = $this->appFormBuilder->create($entity, $formOptions);
        $this->symfonyFormBuilder = $this->appFormBuilder->buildByModel($this->symfonyFormBuilder, $model, $properties);

        $this->initFileFields($entity);

        return $this;
    }

    /**
     * @return FormView
     */
    public function createView()
    {
        return $this->getForm()->createView();
    }

    /**
     * @param Request $request
     *
     * @return FormSubmitResultDto
     */
    public function submit(Request $request, Model $model)
    {
        $form   = $this->getForm();
        $result = new FormSubmitResultDto();

        $form->handleRequest($request);

        if (false === $result->isSubmitted = $form->isSubmitted()) {
            return $result;
        }

        if (false === $result->isValid = $form->isValid()) {
            $result->errors = (string)$form->getErrors(true);

            return $result;
        }

        $this->uploadFiles();
        $this->updateMappedRelationshipForCollection($model);

        return $result;
    }

    /**
     * When creating a form to edit an already persisted item, the file form type still expects a  File instance.
     * As the persisted entity now contains only the relative file path, you first have to concatenate the configured
     * upload path with the stored filename and create a new File class.
     *
     * @param object $entity
     */
    private function initFileFields($entity)
    {
        $accessor = new PropertyAccessor();

        /** @var FormBuilderInterface $field */
        foreach ($this->symfonyFormBuilder as $field) {
            if ($field->getType()->getName() === 'file') {
                $fieldName = $field->getName();

                $file = $accessor->getValue($entity, $fieldName);
                if (!is_string($file)) {
                    continue;
                }

                // Avoid erasing field value when form will saved without new file
                $this->fileFieldsSnapshot[$fieldName] = $file;

                $file = new File($this->uploadDirectory . str_replace($this->uploadFilePrefix, '', $file));

                $accessor->setValue($entity, $fieldName, $file);
            }
        }
    }

    /**
     *
     */
    private function uploadFiles()
    {
        $data     = $this->symfonyFormBuilder->getData();
        $accessor = new PropertyAccessor();

        /** @var FormBuilderInterface $field */
        foreach ($this->symfonyFormBuilder as $field) {
            if ($field->getType()->getName() === 'file') {
                $fieldName = $field->getName();

                /** @var UploadedFile $file */
                $file = $accessor->getValue($data, $fieldName);
                if (!($file instanceof UploadedFile)) {

                    // Avoid erasing field value when form will saved without new file
                    if (isset($this->fileFieldsSnapshot[$fieldName])) {
                        $accessor->setValue($data, $fieldName, $this->fileFieldsSnapshot[$fieldName]);
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
     * @param Model $model Model
     */
    private function updateMappedRelationshipForCollection(Model $model)
    {
        $data     = $this->symfonyFormBuilder->getData();
        $accessor = new PropertyAccessor();

        /** @var FormBuilderInterface $field */
        foreach ($this->symfonyFormBuilder as $field) {
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