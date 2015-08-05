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

use Symfony\Component\Translation\MessageCatalogueInterface;

interface MessageCatalogueLoader
{
    /**
     * Load a message catalogue.
     *
     * @param string $locale The locale to load.
     *
     * @return MessageCatalogueInterface The message catalogue
     */
    public function loadCatalogue($locale);
}
