<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use \ILIAS\UI\Implementation\Component\Menu;
use \ILIAS\UI\Implementation\Component as I;
use \ILIAS\UI\Component as C;

/**
 * Tests for the Drilldown.
 */
class DrilldownTest extends ILIAS_UI_TestBase
{
    public function getUIFactory()
    {
        $factory = new class extends NoUIFactory {
            public function menu() : C\Menu\Factory
            {
                return new Menu\Factory(
                    new I\SignalGenerator()
                );
            }
            public function button()
            {
                return new I\Button\Factory();
            }
            public function legacy($content)
            {
                return new I\Legacy\Legacy(
                    $content,
                    new I\SignalGenerator()
                );
            }
            public function symbol() : \ILIAS\UI\Component\Symbol\Factory
            {
                return new I\Symbol\Factory(
                    new I\Symbol\Icon\Factory(),
                    new I\Symbol\Glyph\Factory(),
                    new I\Symbol\Avatar\Factory()
                );
            }
        };
        return $factory;
    }

    public function setUp() : void
    {
        $icon_factory = new I\Symbol\Icon\Factory();
        $glyph_factory = new I\Symbol\Glyph\Factory();
        $button_factory = new I\Button\Factory();
        $divider_factory = new I\Divider\Factory();
        $this->icon = $icon_factory->standard('', '');
        $this->glyph = $glyph_factory->user('');
        $this->button = $button_factory->standard('', '');
        $this->divider = $divider_factory->horizontal();
        $this->legacy = $this->getUIFactory()->legacy('');
    }

    public function testConstruction()
    {
        $f = $this->getUIFactory();
        $menu = $f->menu()->drilldown('root', []);
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\Menu\\Menu",
            $menu
        );
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\Menu\\Drilldown",
            $menu
        );

        return $menu;
    }

    /**
     * @depends testConstruction
     */
    public function testGetLabel($menu)
    {
        $this->assertEquals(
            'root',
            $menu->getLabel()
        );
    }

    /**
     * @depends testConstruction
     */
    public function testGetItems($menu)
    {
        $this->assertEquals(
            [],
            $menu->getItems()
        );
    }

    public function testWithEntries()
    {
        $f = $this->getUIFactory();
        $items = array(
            $f->menu()->sub('sub', [
                $this->button,
                $this->glyph
            ]),
            $this->divider,
            $this->button
        );
        $menu = $f->menu()->drilldown('root', $items);
        $this->assertEquals(
            $items,
            $menu->getItems()
        );
        return $menu;
    }

    public function testWithWrongEntry()
    {
        $this->expectException(\InvalidArgumentException::class);
        $f = $this->getUIFactory();
        $menu = $f->menu()->drilldown('label', [$this->legacy]);
    }

    /**
     * @depends testWithEntries
     */
    public function testRendering($menu)
    {
        $r = $this->getDefaultRenderer();
        $html = $r->render($menu);
        $expected = <<<EOT
<div class="il-drilldown" id="id_2"> 
    <header class="show-title show-backnav"> 
        <h2>root</h2> 
        <div class="backnav">
            <button class="btn btn-bulky" id="id_1" ><span class="glyph" aria-label="collapse/back" role="img"><span class="glyphicon glyphicon-triangle-left" aria-hidden="true"></span></span><span class="bulky-label"></span></button>
        </div> 
    </header>
    <ul> 
        <li> 
            <button class="menulevel" aria-expanded="false">root<span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button>
            <ul>
                <li> 
                    <button class="menulevel" aria-expanded="false">sub<span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button>
                    <ul>
                        <li>
                            <button class="btn btn-default" data-action=""></button>
                        </li>
                        <li>
                            <a class="glyph" href="" aria-label="show_who_is_online"><span class="glyphicon glyphicon-user" aria-hidden="true"></span></a>
                        </li>
                    </ul>
                </li>
                <li>
                    <hr />
                </li>
                <li>
                    <button class="btn btn-default" data-action=""></button>
                </li>
            </ul> 
        </li> 
    </ul>
</div>
EOT;

        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }
}
