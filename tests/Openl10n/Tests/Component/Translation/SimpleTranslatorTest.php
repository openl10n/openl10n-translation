<?php

namespace Openl10n\Tests\Component\Translation;

use Openl10n\Component\Translation\SimpleTranslator;
use PHPUnit_Framework_TestCase as TestCase;
use Symfony\Component\Translation\MessageCatalogue;

class SimpleTranslatorTest extends TestCase
{
    /** @test */
    public function it_should_translate_simple_message()
    {
        $catalogueLoader = $this->prophesize('Openl10n\Component\Translation\MessageCatalogue\MessageCatalogueLoader');

        $translator = new SimpleTranslator('en', $catalogueLoader->reveal());

        $catalogueLoader->loadCatalogue('en')
            ->willReturn(new MessageCatalogue('en', [
                'messages' => ['foo' => 'bar']
            ]));

        $this->assertEquals('bar', $translator->trans('foo'));
    }

    /** @test */
    public function it_should_translate_plural_message()
    {
        $catalogueLoader = $this->prophesize('Openl10n\Component\Translation\MessageCatalogue\MessageCatalogueLoader');

        $translator = new SimpleTranslator('en', $catalogueLoader->reveal());

        $catalogueLoader->loadCatalogue('en')
            ->willReturn(new MessageCatalogue('en', [
                'messages' => ['foo' => '{0}No foo|{1}One foo|]1,Inf]Many foo']
            ]));

        $this->assertEquals('No foo', $translator->transChoice('foo', 0));
        $this->assertEquals('One foo', $translator->transChoice('foo', 1));
        $this->assertEquals('Many foo', $translator->transChoice('foo', 2));
    }
}
