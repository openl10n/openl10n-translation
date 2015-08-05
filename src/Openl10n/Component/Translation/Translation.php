<?php

namespace Openl10n\Component\Translation;

final class Translation
{
    // unit
    private $key;
    private $context;
    private $source;

    // translation
    private $locale;
    private $target;

    // meta
    private $lastUpdate;
    private $minLenght;
    private $maxLenght;
    private $description;
    private $comments;
    private $format;
    private $references;
    private $tags;
    private $status; // unit vs target
}
