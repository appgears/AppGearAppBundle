<?php

namespace AppGear\AppBundle\Form;

use AppGear\AppBundle\Storage\Storage;
use AppGear\CoreBundle\Entity\Model;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;

/**
 * ModelChoiceLoader manage choices for the model
 */
class ModelChoiceLoader implements ChoiceLoaderInterface
{
    /**
     * Storage
     *
     * @var Storage
     */
    protected $storage;

    /**
     * Model
     *
     * @var Model
     */
    private $model;

    /**
     * Constructor
     *
     * @param Storage $storage Storage
     * @param Model   $model   Model
     */
    public function __construct(Storage $storage, Model $model)
    {
        $this->storage = $storage;
        $this->model   = $model;
    }

    /**
     * {@inheritdoc}
     */
    public function loadChoiceList($value = null)
    {
        $items = $this->storage->getRepository($this->model->getName())->findAll();

        $choices = [];
        foreach ($items as $item) {
            $choices[(string)$item] = $item;
        }

        ksort($choices);

        $list = new ArrayChoiceList($choices, function ($el) {
            return $el->getId();
        });

        return $list;
    }

    /**
     * {@inheritdoc}
     */
    public function loadChoicesForValues(array $values, $value = null)
    {
        if (empty($values)) {
            return array();
        }

        $values = array_map(
            function ($item) {
                if (is_object($item)) {
                    return (string)$item->getId();
                }

                return $item;
            },
            $values
        );

        return $this->loadChoiceList($value)->getChoicesForValues($values);
    }

    /**
     * {@inheritdoc}
     */
    public function loadValuesForChoices(array $choices, $value = null)
    {
        $values = [];

        // Maintain order and indices of the given objects
        foreach ($choices as $i => $object) {
            if (is_object($object)) {
                $values[$i] = (string)$object->getId();
            } else {
                $values[$i] = (string)$object;
            }
        }

        return $values;
    }
}
