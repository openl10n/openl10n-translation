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
    protected $catalogueLoader;

    /**
     * Fallback locales.
     *
     * @var array
     */
    protected $fallbackLocales = array();

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
        $catalogues = [];

        $catalogues[$locale] = $this->catalogueLoader->loadCatalogue($locale);

        $current = $catalogues[$locale];

        foreach ($this->computeFallbackLocales($locale) as $fallback) {
            if (!isset($catalogues[$fallback])) {
                $catalogues[$fallback] = $this->catalogueLoader->loadCatalogue($fallback);
            }

            $current->addFallbackCatalogue($catalogues[$fallback]);
            $current = $catalogues[$fallback];
        }

        return $catalogues[$locale];
    }

    protected function computeFallbackLocales($locale)
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
