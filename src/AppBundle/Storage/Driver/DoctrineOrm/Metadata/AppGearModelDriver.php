<?php

namespace AppGear\AppBundle\Storage\Driver\DoctrineOrm\Metadata;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;

class AppGearModelDriver implements MappingDriver
{
    /**
     * {@inheritdoc}
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata)
    {
        $a = 1;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllClassNames()
    {
        $a = 2;
    }

    /**
     * {@inheritdoc}
     */
    public function isTransient($className)
    {
        return true;
    }
}