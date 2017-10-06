<?php

namespace AppGear\AppBundle\Twig;

use AppGear\AppBundle\Entity\View;
use AppGear\AppBundle\View\ViewManager;
use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\EntityService\ModelService;
use AppGear\CoreBundle\Model\ModelManager;
use Embera\Embera;
use League\CommonMark\CommonMarkConverter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_Extension;
use Twig_SimpleFilter;

/**
 * Twig extension for view
 */
class ViewExtension extends Twig_Extension
{
    /**
     * Service container
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * View manager
     *
     * @var ViewManager
     */
    private $viewManager;

    /**
     * Model manager
     *
     * @var ModelManager
     */
    private $modelManager;

    /**
     * ViewExtension constructor.
     *
     * @param ContainerInterface $container    Service container
     * @param ViewManager        $viewManager  View manager
     * @param ModelManager       $modelManager Model manager
     */
    public function __construct(ContainerInterface $container, ModelManager $modelManager)
    {
        $this->container    = $container;
        //$this->viewManager  = $viewManager;
        $this->modelManager = $modelManager;
    }

    /**
     * * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new Twig_SimpleFilter('embed', array($this, 'embed'), array('is_safe' => array('html'))),
            new Twig_SimpleFilter('markdown', array($this, 'markdown'), array('is_safe' => array('html'))),
            new Twig_SimpleFilter('render', array($this, 'render')),
            new Twig_SimpleFilter('model', array($this, 'model')),
            new Twig_SimpleFilter('view_fields', array($this, 'viewFields')),
        );
    }

    /**
     * * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('widget_service', array($this, 'renderWidgetService')),
        );
    }

    /**
     * Render the view
     *
     * @param View $view View
     *
     * @return string
     */
    public function render($view)
    {
        //return $this->viewManager->getViewService($view)->render();
    }

    /**
     * Render markdown to html
     *
     * @param string $markdown Markdown text
     *
     * @return string
     */
    public function markdown($markdown)
    {
        return (new CommonMarkConverter())->convertToHtml($markdown);
    }

    /**
     * Embeds known/available services into the given text.
     *
     * @param string $html HTML
     *
     * @return string
     */
    public function embed($html)
    {
        return (new Embera())->autoEmbed($html);
    }

    /**
     * Return model for object
     *
     * @param object $name Object
     */
    public function model($name)
    {
        return $this->modelManager->getByInstance($name);
    }

    /**
     * Render widget service
     *
     * @param object            $entity Entity
     * @param View\Field\Widget $widget Widget
     */
    public function renderWidgetService($entity, View\Field\Widget $widget)
    {
        if ($widget instanceof View\Field\Widget\Service) {
            return $this->container->get($widget->getId())->render($entity, $widget);
        }

        return null;
    }

    /**
     * Get view fields
     *
     * @param array $fields
     * @param Model $model
     *
     * @return array
     */
    public function viewFields(array $fields, Model $model)
    {
        return array_map(
            function ($field) use ($model) {
                /** @var View\Field $field */

                $mapping = $field->getMapping();
                $mapping = isset($mapping) ? $mapping : $field->getName();

                if (null !== $mapping) {
                    $parts = \explode('.', $mapping);

                    foreach ($parts as $part) {
                        $modelService = new ModelService($model);
                        $property     = $modelService->getProperty($part);

                        if ($property instanceof Relationship) {
                            $model = $property->getTarget();
                        }
                    }
                } else {
                    $property = (new ModelService($this->getModel()))->getProperty($field->getName());
                }

                return [
                    'name'     => $field->getName(),
                    'mapping'  => $mapping,
                    'property' => $property,
                    'widget'   => $field->getWidget()
                ];
            },
            $fields
        );
    }

    /**
     * {@inheritdoc};
     */
    public function getName()
    {
        return 'appgear_app_view_extension';
    }
}