<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

use ILIAS\UI\Implementation\Component\Listing;
use ILIAS\UI\Implementation\Component\Symbol\Glyph;
use ILIAS\UI\Component as I;

class PropertyListingTest extends ILIAS_UI_TestBase
{
    use LanguageStubs;

    protected function getListingFactory(): Listing\Factory
    {
        return new Listing\Factory();
    }

    protected function getGlyphFactory(): Glyph\Factory
    {
        return new Glyph\Factory($this->createRelayArgumentLanguageStub());
    }

    public function testPropertyListingConstruction(): void
    {
        $pl = $this->getListingFactory()->property();
        $this->assertInstanceOf(I\Listing\Listing::class, $pl);
        $this->assertInstanceOf(I\Listing\Property::class, $pl);
    }

    public function testPropertyListingWithProperty(): void
    {
        $props = [
            ['label1', 'value1', true],
            ['label2', 'value2', false]
        ];
        $pl = $this->getListingFactory()->property()
            ->withProperty(...$props[0])
            ->withProperty(...$props[1]);

        $created_items = $pl->getItems();

        $this->assertEquals($props, $created_items);
    }

    public function testPropertyListingWithItems(): void
    {
        $props = [
            ['label1', 'value1', true],
            ['label2', 'value2', false]
        ];
        $pl = $this->getListingFactory()->property()
            ->withProperty('overwritten', 'by props');

        $pl = $pl->withItems($props);
        $this->assertEquals($props, $pl->getItems());
    }

    public function testPropertyListingWithSymbols(): void
    {
        $symbol = $this->getGlyphFactory()->user();
        $props = [
            [$symbol, 'value1', true],
            ['label2', $symbol, false],
        ];
        $pl = $this->getListingFactory()->property();

        $pl = $pl->withItems($props);
        $this->assertEquals($props, $pl->getItems());
    }

    public function testPropertyListingRendering(): void
    {
        $props = [
            ['label1', 'value1', true],
            ['label2', 'value2', false]
        ];
        $pl = $this->getListingFactory()->property()
            ->withItems($props);

        $expected = $this->brutallyTrimHTML(<<<HTML
            <div class="l-bar__space-keeper c-listing-property">
               <div class="l-bar__group c-listing-property__property">
                  <div class="l-bar__element c-listing-property__propertylabel">label1</div>
                  <div class="l-bar__element c-listing-property__propertyvalue">value1</div>
               </div>
               <div class="l-bar__group c-listing-property__property">
                  <div class="l-bar__element c-listing-property__propertyvalue">value2</div>
               </div>
            </div>
        HTML);

        $this->assertEquals(
            $expected,
            $this->brutallyTrimHTML($this->getDefaultRenderer()->render($pl))
        );
    }

    public function testPropertyListingLongValue(): void
    {
        $props = [
            ['label1', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.  Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi.', true],
            ['label2', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.  Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi.', false]
        ];
        $pl = $this->getListingFactory()->property()
            ->withItems($props);

        $expected = $this->brutallyTrimHTML(<<<HTML
                <div class="l-bar__space-keeper c-listing-property">
                   <div class="l-bar__group c-listing-property__property">
                      <div class="l-bar__element c-listing-property__propertylabel">label1</div>
                      <div aria-hidden="true" class="l-bar__element c-listing-property__propertyvalue t-text-more-less"><span class="t-text-more-less__text-body">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi.</span><input class="t-text-more-less__toggle" type="checkbox" id="id_1"><label class="t-text-more-less__label" for="id_1"><span class="t-text-more-less__label__more">show_more</span><span class="t-text-more-less__label__less">show_less</span></label></div>
                      <div class="l-bar__element sr-only">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi.</div>
                   </div>
                   <div class="l-bar__group c-listing-property__property">
                      <div aria-hidden="true" class="l-bar__element c-listing-property__propertyvalue t-text-more-less"><span class="t-text-more-less__text-body">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi.</span><input class="t-text-more-less__toggle" type="checkbox" id="id_2"><label class="t-text-more-less__label" for="id_2"><span class="t-text-more-less__label__more">show_more</span><span class="t-text-more-less__label__less">show_less</span></label></div>
                      <div class="l-bar__element sr-only">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi.</div>
                   </div>
                </div>
            HTML);

        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($this->getDefaultRenderer()->render($pl))
        );
    }

    public function testPropertyListingSymbolsRendering(): void
    {
        $symbol = $this->getGlyphFactory()->user();
        $props = [
            [$symbol, 'value1'],
            ['label2', $symbol],
        ];

        $pl = $this->getListingFactory()->property()
            ->withItems($props);

        $expected = $this->brutallyTrimHTML(<<<HTML
            <div class="l-bar__space-keeper c-listing-property">
               <div class="l-bar__group c-listing-property__property">
                  <div class="l-bar__element c-listing-property__propertylabel"><a class="glyph" aria-label="show_who_is_online"><span class="glyphicon glyphicon-user" aria-hidden="true"></span></a></div>
                  <div class="l-bar__element c-listing-property__propertyvalue">value1</div>
               </div>
               <div class="l-bar__group c-listing-property__property">
                  <div class="l-bar__element c-listing-property__propertylabel">label2</div>
                  <div class="l-bar__element c-listing-property__propertyvalue"><a class="glyph" aria-label="show_who_is_online"><span class="glyphicon glyphicon-user" aria-hidden="true"></span></a></div>
               </div>
            </div>
        HTML);

        $this->assertEquals(
            $expected,
            $this->brutallyTrimHTML($this->getDefaultRenderer()->render($pl))
        );
    }

}
