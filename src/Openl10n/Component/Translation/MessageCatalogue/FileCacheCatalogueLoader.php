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
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\MessageCatalogueInterface;

/**
 * Port of Symfony file cache system.
 */
class FileCacheCatalogueLoader implements MessageCatalogueLoader
{
    /**
     * Decorated catalogue loader.
     *
     * @var MessageCatalogueLoader
     */
    private $catalogueLoader;

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @param MessageCatalogueLoader $catalogueLoader
     * @param string                 $cacheDir
     * @param bool                   $debug
     */
    public function __construct(MessageCatalogueLoader $catalogueLoader, $cacheDir, $debug = false)
    {
        $this->catalogueLoader = $catalogueLoader;
        $this->cacheDir = $cacheDir;
        $this->debug = (bool) $debug;
    }

    /**
     * {@inheritdoc}
     */
    public function loadCatalogue($locale)
    {
        if (!class_exists('Symfony\Component\Config\ConfigCache')) {
            throw new BadMethodCallException('You must install symfony/config component to use this class');
        }

        $cache = new ConfigCache($this->getCatalogueCachePath($locale), $this->debug);

        if ($cache->isFresh()) {
            $catalogue = include $cache->getPath();
        } else {
            $catalogue = $this->catalogueLoader->loadCatalogue($locale);

            $cache->write(
                $this->dumpCacheContent($catalogue),
                $catalogue->getResources()
            );
        }

        return $catalogue;
    }

    private function getCatalogueCachePath($locale)
    {
        return $this->cacheDir.'/catalogue.'.$locale.'.php';
    }

    /**
     * @return string Content to write in cache file
     */
    private function dumpCacheContent(MessageCatalogueInterface $catalogue)
    {
        $currentCatalogue = $catalogue;
        $currentLocale = '';
        $fallbackContent = '';
        $replacementPattern = '/[^a-z0-9_]/i';

        while (null !== $fallbackCatalogue = $currentCatalogue->getFallbackCatalogue()) {
            $fallbackLocale = $fallbackCatalogue->getLocale();

            $fallbackSuffix = ucfirst(preg_replace($replacementPattern, '_', $fallbackLocale));
            $currentSuffix = ucfirst(preg_replace($replacementPattern, '_', $currentLocale));

            $fallbackContent .= sprintf(<<<EOF
\$catalogue%s = new MessageCatalogue('%s', %s);
\$catalogue%s->addFallbackCatalogue(\$catalogue%s);


EOF
                ,
                $fallbackSuffix,
                $fallbackLocale,
                var_export($fallbackCatalogue->all(), true),
                $currentSuffix,
                $fallbackSuffix
            );

            $currentCatalogue = $fallbackCatalogue;
            $currentLocale = $fallbackLocale;
        }

        $content = sprintf(<<<EOF
<?php

use Symfony\Component\Translation\MessageCatalogue;

\$catalogue = new MessageCatalogue('%s', %s);

%s
return \$catalogue;

EOF
            ,
            $catalogue->getLocale(),
            var_export($catalogue->all(), true),
            $fallbackContent
        );

        return $content;
    }
}
