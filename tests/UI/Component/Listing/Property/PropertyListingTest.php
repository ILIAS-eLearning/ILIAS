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
use ILIAS\UI\Component as I;

class PropertyListingTest extends ILIAS_UI_TestBase
{
    protected function getListingFactory(): Listing\Factory
    {
        return new Listing\Factory();
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

        $this->assertEquals($props, $pl->getItems());
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

    public function testPropertyListingRendering(): void
    {
        $props = [
            ['label1', 'value1', true],
            ['label2', 'value2', false]
        ];
        $pl = $this->getListingFactory()->property()
            ->withItems($props);

        $expected = $this->brutallyTrimHTML('
<div class="l-bar__container c-listing-property">
    <div class="l-bar__group c-listing-property__property">
        <span class="l-bar__element c-listing-property__propertylabel">label1</span>
        <span class="l-bar__element c-listing-property__propertyvalue">value1</span>
    </div>
    <div class="l-bar__group c-listing-property__property">
        <span class="l-bar__element c-listing-property__propertyvalue">value2</span>
    </div>
</div>
        ');

        $this->assertEquals(
            $expected,
            $this->brutallyTrimHTML($this->getDefaultRenderer()->render($pl))
        );
    }
}
