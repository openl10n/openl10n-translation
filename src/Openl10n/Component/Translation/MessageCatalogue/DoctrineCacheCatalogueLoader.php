<?php

/*
 * This file is part of the openl10n package.
 *
 * (c) Matthieu Moquet <matthieu@moquet.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Openl10n\Component\Translation\MessageCatalogue;

use Doctrine\Common\Cache\Cache;

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
    protected $catalogueLoader;

    /**
     * Cache provider.
     *
     * @var Cache
     */
    protected $cache;

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
