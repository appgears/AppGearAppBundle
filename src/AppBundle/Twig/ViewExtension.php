<?php

namespace AppGear\AppBundle\Twig;

use AppGear\AppBundle\Cache\CacheManager;
use AppGear\AppBundle\Entity\View;
use AppGear\AppBundle\View\ViewManager;
use Cosmologist\Gears\Html;
use Embera\Embera;
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
     * @param string $markdown       Markdown text
     *
     * @param bool   $autoEmbed      Does embed known/available services into the given text?
     * @param int    $truncateLength Truncates html with tags preserving by length.
     *                               Set zero if don't need truncating
     * @param string $truncateEnding Append it to the end of truncated html
     *
     * @return string
     */
    public function markdownTypograph($markdown, $autoEmbed = false, $truncateLength = 0, $truncateEnding = '')
    {
        $cacheKey = sprintf('AppGearAppBundleTwigViewExtension_markdown_%u_%u_%s', $autoEmbed, $truncateLength,
            md5($markdown . $truncateEnding));
        if ($this->cacheManager->contains($cacheKey)) {
            return $this->cacheManager->fetch($cacheKey);
        }

        // Convert from markdown to the html
        $converter = new CommonMarkConverter();
        $html      = $converter->convertToHtml($markdown);

        // Auto embed
        if ($autoEmbed) {
            $embera = new Embera();
            $html   = $embera->autoEmbed($html);
        }

        // Decorate images in the html
        $html = $this->decorateMedia($html, 'img', 'article-image');

        // Decorate iframes in the html
        $html = $this->decorateMedia($html, 'iframe', 'thumbnail text-center');

        // Fix bug with encoded image alt-text
        preg_match_all('/alt="(.+?)"/', $html, $matches);
        foreach ($matches[1] as $match) {
            $html = str_replace($match, html_entity_decode($match), $html);
        }

        // Truncate the content
        if ($truncateLength > 0) {
            $html = Html::truncate($html, $truncateLength, $truncateEnding);
        }

        $this->cacheManager->save($cacheKey, $html);

        return $html;
    }

    /**
     * Move media content (images, iframe's etc) from paragraphs to div's with specific class
     *
     * Decorates images with legend from alt.
     *
     * @param string $html     Html
     * @param string $tag      Media tag (img, iframe etc)
     * @param string $divClass Set div class
     *
     * @return string
     */
    private function decorateMedia($html, $tag, $divClass = '')
    {
        $crawler = new Crawler();
        $crawler->addHtmlContent($html, 'UTF-8');

        foreach ($crawler->filter('p ' . $tag) as $media) {
            /** @var \DomElement $media */
            /** @var \DomElement $paragraph */
            if (($paragraph = $media->parentNode) && ($paragraph->childNodes->length !== 1)) {
                continue;
            }

            $div = new \DOMElement('div');
            $paragraph->parentNode->replaceChild($div, $paragraph);
            if (strlen($divClass)) {
                $div->setAttribute('class', $divClass);
            }

            $div->appendChild($media);

            // if media is image and has alt text - add paragraph with this text
            if (($media->tagName === 'img') && ($alt = $media->getAttribute('alt'))) {
                $legend = new \DOMElement('p', $alt);
                $div->appendChild($legend);
            }
        }

        $html = '';
        if ($crawler->getNode(0)) {
            foreach ($crawler->getNode(0)->childNodes->item(0)->childNodes as $childNode) {
                $html .= $childNode->ownerDocument->saveHTML($childNode);
            }
        }

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