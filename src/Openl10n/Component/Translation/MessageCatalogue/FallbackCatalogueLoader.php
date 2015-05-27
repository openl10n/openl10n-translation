<?php

namespace Openl10n\Component\Translation\MessageCatalogue;

use Doctrine\Common\Cache\Cache;
use Symfony\Component\Translation\MessageCatalogueInterface;

/**
 * Combine catalogues using fallback locales.
 */
class FallbackCatalogueLoader implements MessageCatalogueLoader
{
    /**
     * Decorated catalogue loader.
     *
     * @var MessageCatalogueLoader
     */
    private $catalogueLoader;

    /**
     * @var MessageCatalogueInterface[]
     */
    private $catalogues = array();

    /**
     * Fallback locales.
     *
     * @var array
     */
    private $fallbackLocales = array();

    /**
     * @param MessageCatalogueLoader $catalogueLoader
     */
    public function __construct(MessageCatalogueLoader $catalogueLoader)
    {
        $this->catalogueLoader = $catalogueLoader;
    }

    /**
     * Sets the fallback locales.
     *
     * @param array $locales The fallback locales
     */
    public function setFallbackLocales(array $locales)
    {
        // needed as the fallback locales are linked to the already loaded catalogues
        $this->catalogues = array();

        $this->fallbackLocales = $locales;
    }

    /**
     * Gets the fallback locales.
     *
     * @return array $locales The fallback locales
     */
    public function getFallbackLocales()
    {
        return $this->fallbackLocales;
    }

    /**
     * {@inheritdoc}
     */
    public function loadCatalogue($locale)
    {
        // Memoization to load catalogue only once
        if (isset($this->catalogues[$locale])) {
            return $this->catalogues[$locale];
        }

        $this->catalogues[$locale] = $this->catalogueLoader->loadCatalogue($locale);

        $current = $this->catalogues[$locale];

        foreach ($this->computeFallbackLocales($locale) as $fallback) {
            if (!isset($this->catalogues[$fallback])) {
                $this->catalogues[$fallback] = $this->catalogueLoader->loadCatalogue($fallback);
            }

            $current->addFallbackCatalogue($this->catalogues[$fallback]);
            $current = $this->catalogues[$fallback];
        }

        return $this->catalogues[$locale];
    }

    private function computeFallbackLocales($locale)
    {
        $locales = array();
        foreach ($this->fallbackLocales as $fallback) {
            if ($fallback === $locale) {
                continue;
            }

            $locales[] = $fallback;
        }

        if (strrchr($locale, '_') !== false) {
            array_unshift($locales, substr($locale, 0, -strlen(strrchr($locale, '_'))));
        }

        return array_unique($locales);
    }
}
