<?php

namespace AppGear\AppBundle\Form;

use AppGear\AppBundle\Entity\Storage\Column;
use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Entity\Property\Relationship;
use AppGear\CoreBundle\EntityService\ModelService;
use Commerce\PlatformBundle\Entity\Database\NoteFile;
use Cosmologist\Gears\ObjectType;
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
     * RelatedDynamicType constructor.
     *
     * @param FormBuilder  $formBuilder  AppGear form builder
     * @param Relationship $relationship Relationship
     */
    public function __construct(FormBuilder $formBuilder, Relationship $relationship)
    {
        $this->formBuilder  = $formBuilder;
        $this->relationship = $relationship;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $ms = new ModelService($this->relationship->getTarget());

        foreach ($ms->getAllProperties() as $property) {
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
            $target = $relationship->getTarget();
            $ms     = new ModelService($target);

            return $ms->getProperty($backSideName);
        }

        return null;
    }

    public
    function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => NoteFile::class,
        ));
    }
}
