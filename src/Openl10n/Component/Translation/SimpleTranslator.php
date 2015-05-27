<?php

namespace Openl10n\Component\Translation;

use Openl10n\Component\Translation\MessageCatalogue\MessageCatalogueLoader;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Component\Translation\TranslatorInterface;

class SimpleTranslator implements TranslatorInterface, TranslatorBagInterface
{
    /**
     * The default locale.
     *
     * @var string
     */
    private $locale;

    /**
     * The loader of message catalogue.
     *
     * @var MessageCatalogueLoader
     */
    private $catalogueLoader;

    /**
     * The message selector for pluralization.
     *
     * @var MessageSelector
     */
    private $selector;

    /**
     * Constructor.
     *
     * @param string                 $locale          The default locale
     * @param MessageCatalogueLoader $catalogueLoader The loader of message catalogue
     * @param MessageSelector|null   $selector        The message selector for pluralization
     */
    public function __construct($locale, MessageCatalogueLoader $catalogueLoader, MessageSelector $selector = null)
    {
        $this->setLocale($locale);
        $this->catalogueLoader = $catalogueLoader;
        $this->selector = $selector ?: new MessageSelector();
    }

    /**
     * {@inheritdoc}
     */
    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        if (null === $domain) {
            $domain = 'messages';
        }

        $message = $this->getMessage($locale, $id, $domain);

        return strtr($message, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function transChoice($id, $number, array $parameters = array(), $domain = null, $locale = null)
    {
        if (null === $domain) {
            $domain = 'messages';
        }

        $pluralizedMessage = $this->getMessage($locale, $id, $domain);

        $message = $this->selector->choose($pluralizedMessage, (int) $number, $locale);

        return strtr($message, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    public function getCatalogue($locale = null)
    {
        if (null === $locale) {
            $locale = $this->getLocale();
        }

        if (!isset($this->catalogues[$locale])) {
            $this->catalogues[$locale] = $this->catalogueLoader->loadCatalogue($locale);
        }

        return $this->catalogues[$locale];
    }

    /**
     * Retrieve translated message from localized catalogue.
     *
     * @param string $locale Locale
     * @param string $id     Message identifier
     * @param string $domain Domain name
     *
     * @return string Translated message
     */
    protected function getMessage($locale, $id, $domain)
    {
        // Load the catalogue
        $catalogue = $this->getCatalogue($locale);

        // Retrieve the message from the catalogue
        $message = $catalogue->get((string) $id, $domain);

        return $message;
    }
}
