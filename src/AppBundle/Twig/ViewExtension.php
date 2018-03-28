<?php

namespace AppGear\AppBundle\Twig;

use AppGear\AppBundle\Entity\View;
use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Entity\Property\Relationship;
use AppGear\CoreBundle\Helper\ModelHelper;
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
     * Model manager
     *
     * @var ModelManager
     */
    private $modelManager;

    /**
     * ViewExtension constructor.
     *
     * @param ContainerInterface $container    Service container
     * @param ModelManager       $modelManager Model manager
     */
    public function __construct(ContainerInterface $container, ModelManager $modelManager)
    {
        $this->container    = $container;
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
     * @param View  $view View
     * @param mixed $data Data
     *
     * @return string
     */
    public function render(View $view, $data = null)
    {
        // Avoid circular reference problem
        $viewManager = $this->container->get('appgear.view.manager');

        return $viewManager->render($view, ['data' => $data]);
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
     *
     * @return Model
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
     *
     * @return null
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
     * @param array  $fields
     * @param object $entity
     * @param bool   $getFromModelIfNoFields
     *
     * @return array
     */
    public function viewFields(array $fields, $entity, $getFromModelIfNoFields = true)
    {
        $model = is_object($entity) ? $this->modelManager->getByInstance($entity) : null;

        if (count($fields) === 0 && $getFromModelIfNoFields) {
            $fields = $this->getFieldsFromModel($model);
        }

        return array_map(
            function ($field) use ($model) {
                /** @var View\Field $field */

                $mapping  = $field->getMapping() ?? $field->getName();
                $property = ModelHelper::getProperty($model, $mapping);

                return [
                    'field'    => $field,
                    'name'     => $field->getName(),
                    'mapping'  => $mapping,
                    'property' => $property,
                    'widget'   => $field->getWidget(),
                    'group'    => $field->getGroup()
                ];
            },
            $fields
        );
    }

    /**
     * @return array
     */
    private function getFieldsFromModel(Model $model)
    {
        return array_map(
            function ($property) {
                $viewField = new View\Field();
                $viewField
                    ->setName($property->getName())
                    ->setMapping($property->getName());

                return $viewField;
            },
            iterator_to_array(ModelHelper::getProperties($model))
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