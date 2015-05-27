<?php

namespace Openl10n\Component\Translation\MessageCatalogue;

use Symfony\Component\Translation\Loader\LoaderInterface as ResourceLoader;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * Load catalogue from resources.
 */
class ResourceCatalogueLoader implements MessageCatalogueLoader
{
    /**
     * @var MessageCatalogueInterface[]
     */
    private $catalogues = array();

    /**
     * @var LoaderInterface[]
     */
    private $loaders = array();

    /**
     * @var array
     */
    private $resources = array();

    /**
     * Adds a Loader.
     *
     * @param string          $format The name of the loader (@see addResource())
     * @param LoaderInterface $loader A LoaderInterface instance
     */
    public function addResourceLoader($format, ResourceLoader $loader)
    {
        $this->loaders[$format] = $loader;
    }

    /**
     * Adds a Resource.
     *
     * @param string $format   The name of the loader (@see addLoader())
     * @param mixed  $resource The resource name
     * @param string $locale   The locale
     * @param string $domain   The domain
     *
     * @throws \InvalidArgumentException If the locale contains invalid characters
     */
    public function addResource($format, $resource, $locale, $domain = null)
    {
        if (null === $domain) {
            $domain = 'messages';
        }

        $this->resources[$locale][] = array($format, $resource, $domain);

        unset($this->catalogues[$locale]);
    }

    /**
     * Traverses all resources to fetch messages only for the given locale.
     *
     * {@inheritdoc}
     */
    public function loadCatalogue($locale)
    {
        // Memoization to load catalogue only once
        if (isset($this->catalogues[$locale])) {
            return $this->catalogues[$locale];
        }

        // Create a new catalogue
        $this->catalogues[$locale] = new MessageCatalogue($locale);

        // Traverse resources to complete catalogue
        $resources = isset($this->resources[$locale]) ? $this->resources[$locale] : [];
        foreach ($resources as $resource) {
            $name = $resource[0];
            $format = $resource[1];
            $domain = $resource[2];

            if (!isset($this->loaders[$name])) {
                throw new \RuntimeException("The \"$name\" translation loader is not registered.");
            }

            $catalogue = $this->loaders[$name]->load($format, $locale, $domain);

            $this->catalogues[$locale]->addCatalogue($catalogue);
        }

        return $this->catalogues[$locale];
    }
}
