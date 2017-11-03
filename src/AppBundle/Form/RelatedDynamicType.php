<?php

namespace AppGear\AppBundle\Form;

use AppGear\AppBundle\Entity\Storage\Column;
use AppGear\CoreBundle\Entity\Property\Relationship;
use AppGear\CoreBundle\EntityService\PropertyService;
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
        foreach (ModelHelper::getProperties($this->relationship->getTarget()) as $property) {
            $backSideProperty = null;
            if ($property instanceof Relationship) {
                $backSideProperty = $this->getBacksideProperty($property);
            }

            if ($backSideProperty !== null && $backSideProperty === $this->relationship) {
                continue;
            }

            $this->formBuilder->addProperty($builder, $property);
        }
    }

    private function getBacksideProperty(Relationship $relationship)
    {
        $backSideName = null;

        $extension = (new PropertyService($relationship))->getExtension(Column::class);
        if ($extension !== null) {
            if (strlen($mappedBy = $extension->getMappedBy())) {
                $backSideName = $mappedBy;
            } elseif (strlen($inversedBy = $extension->getInversedBy())) {
                $backSideName = $inversedBy;
            }
        }

        if ($backSideName !== null) {

            return ModelHelper::getProperty($relationship->getTarget(), $backSideName);
        }

        return null;
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
