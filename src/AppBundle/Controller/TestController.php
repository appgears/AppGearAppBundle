<?php

namespace AppGear\AppBundle\Controller;

use AppGear\AppBundle\Storage\Storage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TestController
{
    /**
     * @var Storage
     */
    private $storage;

    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    public function testAction(Request $request)
    {
        $model = $this->storage->find('core.model', 'core.model');

        return new Response('done');
    }
}