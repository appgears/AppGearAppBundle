<?php
/**
 * Created by PhpStorm.
 * User: pavellevin
 * Date: 20.01.17
 * Time: 17:23
 */

namespace AppGear\AppBundle\Storage\Driver\Yaml\Hydrator;

use AppGear\AppBundle\Storage\Driver\Yaml\Hydrator;
use AppGear\CoreBundle\Entity\Property\Field;
use AppGear\CoreBundle\Entity\Property\Relationship;
use AppGear\CoreBundle\Helper\ModelHelper;
use AppGear\CoreBundle\Model\ModelManager;
use RuntimeException;

class SimpleHydrator implements Hydrator
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * SimpleHydrator constructor.
     *
     * @param ModelManager $modelManager
     */
    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate(string $model, array $data)
    {
        return $this->initialize($data, $model);
    }

    private function initialize(array $data, string $model = null)
    {
        if (array_key_exists('type', $data)) {
            $model = $data['type'];
        }

        if ($model === null) {
            throw new RuntimeException(
                sprintf('Can not determine the model for parameters section: %s', var_export($data, true))
            );
        }

        $instance = $this->modelManager->instance($model);
        $model    = $this->modelManager->get($model);

        foreach (ModelHelper::getProperties($model) as $property) {
            $propertyName = $property->getName();

            if (array_key_exists($propertyName, $data)) {
                $value              = null;
                $propertyParameters = $data[$propertyName];

                if ($property instanceof Field) {
                    $value = $propertyParameters;
                } elseif ($property instanceof Relationship\ToOne) {
                    $value = $this->initialize($propertyParameters, $property->getTarget()->getName());
                } elseif ($property instanceof Relationship\ToMany) {
                    $value = [];
                    foreach ($propertyParameters as $subParameters) {
                        if (is_scalar($subParameters)) {
                            $subParameters = ['_id' => $subParameters];
                        }
                        $value[] = $this->initialize($subParameters, $property->getTarget()->getName());

                    }
                }

                $instance->{'set' . ucfirst($propertyName)}($value);
            }
        }

        return $instance;
    }
}