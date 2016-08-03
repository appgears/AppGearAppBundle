<?php

namespace AppGear\AppBundle\Storage\Platform;

interface MysqlFieldTypeServiceInterface
{
    /**
     * Return mysql field type
     *
     * @return string
     */
    public function getMysqlFieldType();
}
