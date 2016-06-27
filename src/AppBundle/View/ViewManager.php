<?php

namespace AppGear\AppBundle\View;

use AppGear\AppBundle\Entity\View;
use AppGear\AppBundle\EntityService\ViewService;
use AppGear\CoreBundle\EntityService\ModelService;
use AppGear\CoreBundle\Model\ModelManager;
use AppGear\PlatformBundle\Service\TaggedManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ViewManager
{
    /**
     * Tagged service manager
     *
     * @var TaggedManager
     */
    private $taggedManager;

    /**
     * Service container
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * Model manager
     *
     * @var ModelManager
     */
    private $modelManager;

    /**
     * ViewManager constructor.
     *
     * @param ModelManager            $modelManager  Model manager
     * @param TaggedManager      $taggedManager Tagged service manager
     * @param ContainerInterface $container     Service container
     */
    public function __construct(ModelManager $modelManager, TaggedManager $taggedManager, ContainerInterface $container)
    {
        $this->modelManager  = $modelManager;
        $this->taggedManager = $taggedManager;
        $this->container     = $container;
    }

    /**
     * Find and return service for view
     *
     * @param View $view View
     *
     * @return ViewService
     */
    public function getViewService(View $view)
    {
        $viewModel    = $this->modelManager->getByInstance($view);
        $modelService = new ModelService($viewModel);

        foreach ($modelService->getSelfAndParents() as $model) {
            $services = $this->taggedManager->findServices('appgear.entity_service.view', ['model' => $model->getName()]);
            if (count($services) > 0) {
                $viewService = $this->container->get($services[0]['id']);
                $viewService->setView($view);
                return $viewService;
            }
        }

        throw new \RuntimeException(sprintf('View service for "%s" not found', $viewModel->getName()));
    }
}