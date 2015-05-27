<?php

namespace Openl10n\Component\Translation\MessageCatalogue;

interface MessageCatalogueLoader
{
    /**
     * Load a message catalogue
     *
     * @param string $locale The locale to load.
     *
     * @return MessageCatalogueInterface The message catalogue
     */
    public function loadCatalogue($locale);
}
