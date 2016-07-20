<?php

namespace AppGear\AppBundle\Twig;

use AppGear\AppBundle\Cache\CacheManager;
use AppGear\AppBundle\Entity\View;
use AppGear\AppBundle\View\ViewManager;
use League\CommonMark\CommonMarkConverter;
use Symfony\Component\DomCrawler\Crawler;
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
     * Cache manager
     *
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * ViewExtension constructor.
     *
     * @param ViewManager  $viewManager  View manager
     * @param CacheManager $cacheManager Cache manager
     */
    public function __construct(ViewManager $viewManager, CacheManager $cacheManager)
    {
        $this->viewManager  = $viewManager;
        $this->cacheManager = $cacheManager;
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
        $cacheKey = 'markdown_' . md5($markdown);
        if ($this->cacheManager->contains($cacheKey)) {
            return $this->cacheManager->fetch($cacheKey);
        }

        $converter = new CommonMarkConverter();
        $html      = $converter->convertToHtml($markdown);

        $crawler = new Crawler();
        $crawler->addHtmlContent($html, 'UTF-8');

        foreach ($crawler->filter('p img') as $image) {
            /** @var \DomElement $image */
            /** @var \DomElement $paragraph */
            if (($paragraph = $image->parentNode) && ($paragraph->childNodes->length !== 1)) {
                continue;
            }

            $div = new \DOMElement('div');
            $paragraph->parentNode->replaceChild($div, $paragraph);
            $div->setAttribute('class', 'article-image');

            $div->appendChild($image);

            // if image has alt text - add paragraph with this text
            if ($alt = $image->getAttribute('alt')) {
                $legend = new \DOMElement('p', $alt);
                $div->appendChild($legend);
            }
        }

        $html = '';
        foreach ($crawler->getNode(0)->childNodes->item(0)->childNodes as $childNode) {
            $html .= $childNode->ownerDocument->saveXML($childNode);
        }

        // Fix bug with encoded image alt-text
        preg_match_all('/alt="(.+?)"/', $html, $matches);
        foreach ($matches[1] as $match) {
            $html = str_replace($match, html_entity_decode($match), $html);
        }

        $this->cacheManager->save($cacheKey, $html);

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