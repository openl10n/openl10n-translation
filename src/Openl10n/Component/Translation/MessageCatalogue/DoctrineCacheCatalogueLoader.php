<?php

namespace Openl10n\Component\Translation\MessageCatalogue;

use Doctrine\Common\Cache\Cache;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\MessageCatalogueInterface;

/**
 * Catalogue loader using the doctrine/cache component.
 */
class DoctrineCacheCatalogueLoader implements MessageCatalogueLoader
{
    /**
     * Decorated catalogue loader.
     *
     * @var MessageCatalogueLoader
     */
    private $catalogueLoader;

    /**
     * Cache provider.
     *
     * @var Cache
     */
    private $cache;

    /**
     * @param MessageCatalogueLoader $catalogueLoader
     * @param Cache                  $cache
     */
    public function __construct(MessageCatalogueLoader $catalogueLoader, Cache $cache)
    {
        $this->catalogueLoader = $catalogueLoader;
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function loadCatalogue($locale)
    {
        if ($this->cache->contains($locale)) {
            return $this->cache->fetch($locale);
        }

        $catalogue = $this->catalogueLoader->loadCatalogue($locale);

        $this->cache->save($locale, $catalogue);

        return $catalogue;
    }
}
