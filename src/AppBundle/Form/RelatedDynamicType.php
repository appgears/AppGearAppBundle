<?php

namespace AppGear\AppBundle\Form;

use AppGear\AppBundle\Helper\StorageHelper;
use AppGear\CoreBundle\Collection\Collection;
use AppGear\CoreBundle\Entity\Property\Relationship;
use AppGear\CoreBundle\Helper\ModelHelper;
use AppGear\CoreBundle\Model\ModelManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Dynamic symfony form type for relation
 */
class RelatedDynamicType extends AbstractType
{
    /**
     * Relationship
     *
     * @var Relationship
     */
    private $relationship;

    /**
     * AppGear form builder
     *
     * @var FormBuilder
     */
    private $formBuilder;

    /**
     * Model manager
     *
     * @var ModelManager
     */
    private $modelManager;

    /**
     * RelatedDynamicType constructor.
     *
     * @param FormBuilder  $formBuilder  AppGear form builder
     * @param Relationship $relationship Relationship
     * @param ModelManager $modelManager Model manager
     */
    public function __construct(FormBuilder $formBuilder, Relationship $relationship, ModelManager $modelManager)
    {
        $this->formBuilder  = $formBuilder;
        $this->relationship = $relationship;
        $this->modelManager = $modelManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $properties = Collection::create(ModelHelper::getProperties($this->relationship->getTarget()))
            ->filter([StorageHelper::class, 'isRelatedProperty'], [$this->relationship])
            ->toArray();

        foreach ($properties as $property) {
            // Force hidden type for collection items identifier
            $forceType = StorageHelper::isIdentifierProperty($property) ? 'hidden' : null;

            $this->formBuilder->addProperty($builder, $property, [], null, $forceType);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $fqcn = $this->modelManager->fullClassName($this->relationship->getTarget());

        $resolver->setDefaults(['data_class' => $fqcn]);
    }
}
