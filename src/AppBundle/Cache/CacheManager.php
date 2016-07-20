<?php

namespace AppGear\AppBundle\Cache;

use Doctrine\Common\Cache\Cache;

class CacheManager implements Cache
{
    /**
     * Cache item ID of the tag to id map
     */
    const TAGS_ID = '_tags';

    /**
     * Doctrine Cache
     *
     * @var Cache
     */
    protected $cache;

    /**
     * Tags map
     *
     * @var array
     */
    protected $tags;

    /**
     * Constructor
     *
     * @param Cache $cache Doctrine Cache
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;

        $this->loadTags();
    }

    /**
     * Load tags map from the cache
     */
    protected function loadTags()
    {
        if ($this->tags === null) {
            if ($this->cache->contains(self::TAGS_ID)) {
                $this->tags = $this->cache->fetch(self::TAGS_ID);
            }
        }

        if ($this->tags === null) {
            $this->tags = [];
        }
    }

    /**
     * Set tags for the cache ID
     *
     * @param string   $id   The ID
     *
     * @param string[] $tags Tags
     */
    public function setTags($id, $tags)
    {
        // Match each tag with ID
        foreach ($tags as $tag) {
            if (!array_key_exists($tag, $this->tags)) {
                $this->tags[$tag] = [$id];
            } elseif (!in_array($id, $this->tags[$tag])) {
                $this->tags[$tag][] = $id;
            }
        }

        // Save all tags to the cache
        $this->cache->save(self::TAGS_ID, $this->tags);
    }

    /**
     * Deletes a cache entries by tags
     *
     * @param string|string[] $tags Tag name or array of tags names
     */
    public function deleteTags($tags)
    {
        foreach ($tags as $tag) {
            if (array_key_exists($tag, $this->tags)) {
                foreach ($this->tags[$tag] as $id) {
                    $this->cache->delete($id);
                }
                unset($this->tags[$tag]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($id)
    {
        return $this->cache->fetch($id);
    }

    /**
     * {@inheritdoc}
     */
    public function contains($id)
    {
        return $this->cache->contains($id);
    }

    /**
     * {@inheritdoc}
     */
    public function save($id, $data, $lifeTime = 0)
    {
        return $this->cache->save($id, $data, $lifeTime);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        return $this->cache->delete($id);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAll()
    {
        return $this->cache->deleteAll();
    }

    /**
     * {@inheritdoc}
     */
    public function getStats()
    {
        return $this->cache->getStats();
    }
}