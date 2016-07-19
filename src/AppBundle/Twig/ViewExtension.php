<?php

namespace AppGear\AppBundle\Twig;

use AppGear\AppBundle\Entity\View;
use AppGear\AppBundle\View\ViewManager;
use League\CommonMark\CommonMarkConverter;
use Twig_Extension;
use Twig_SimpleFilter;

/**
 * Twig extension for view
 */
class ViewExtension extends Twig_Extension
{
    /**
     * View manager
     *
     * @var ViewManager
     */
    private $viewManager;

    /**
     * ViewExtension constructor.
     *
     * @param ViewManager $viewManager View manager
     */
    public function __construct(ViewManager $viewManager)
    {
        $this->viewManager = $viewManager;
    }

    /**
     * * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new Twig_SimpleFilter('view_render', array($this, 'render')),
            new Twig_SimpleFilter('markdown_typograph', array($this, 'markdownTypograph'), array('is_safe' => array('html'))),
        );
    }

    /**
     * Render the view
     *
     * @param View $view View
     *
     * @return string
     */
    public function render(View $view)
    {
        return $this->viewManager->getViewService($view)->render();
    }

    /**
     * Render markdown to html and processing html
     *
     * @param string $markdown Markdown text
     *
     * @return string
     */
    public function markdownTypograph($markdown)
    {
        $converter = new CommonMarkConverter();
        $html = $converter->convertToHtml($markdown);

        return $html;
    }

    /**
     * {@inheritdoc};
     */
    public function getName()
    {
        return 'appgear_app_view_extension';
    }
}