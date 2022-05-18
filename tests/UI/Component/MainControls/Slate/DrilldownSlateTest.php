<?php declare(strict_types=1);

/* Copyright (c) 2021 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\MainControls\Slate\Drilldown;

/**
 * Tests for the DrilldownSlate.
 */
class DrilldownSlateTest extends ILIAS_UI_TestBase
{
    public function getUIFactory() : NoUIFactory
    {
        return new class extends NoUIFactory {
            protected function getSigGen()
            {
                return new I\SignalGenerator();
            }

            public function menu() : C\Menu\Factory
            {
                return new I\Menu\Factory($this->getSigGen());
            }

            public function symbol() : C\Symbol\Factory
            {
                return new I\Symbol\Factory(
                    new I\Symbol\Icon\Factory(),
                    new I\Symbol\Glyph\Factory(),
                    new I\Symbol\Avatar\Factory()
                );
            }

            public function mainControls() : C\MainControls\Factory
            {
                $slate_factory = new I\MainControls\Slate\Factory(
                    $this->getSigGen(),
                    new I\Counter\Factory(),
                    $this->symbol()
                );
                return new I\MainControls\Factory($this->getSigGen(), $slate_factory);
            }

            public function button() : C\Button\Factory
            {
                return new I\Button\Factory();
            }
        };
    }

    public function testImplementsFactoryInterface() : Drilldown
    {
        $f = $this->getUIFactory();
        $slate = $f->mainControls()->slate()->drilldown(
            "ddslate",
            $f->symbol()->icon()->custom('', ''),
            $f->menu()->drilldown('ddmenu', [])
        );
        $this->assertInstanceOf("ILIAS\\UI\\Component\\MainControls\\Slate\\Drilldown", $slate);
        return $slate;
    }

    /**
     * @depends testImplementsFactoryInterface
     */
    public function testRendering(Drilldown $slate) : void
    {
        $r = $this->getDefaultRenderer();
        $html = $r->render($slate);

        $expected = '

            <div class="il-maincontrols-slate disengaged" id="id_3">
                <div class="il-maincontrols-slate-content" data-replace-marker="content">
                    <div class="il-drilldown" id="id_2">

                        <header class="show-title show-backnav">
                            <h2>ddmenu</h2>
                            <div class="backnav">
                                <button class="btn btn-bulky" id="id_1"><span class="glyph" role="img"><span class="glyphicon glyphicon-triangle-left" aria-hidden="true"></span></span><span class="bulky-label"></span></button>
                            </div>
                        </header>
                        <ul>
                            <li><button class="menulevel" aria-expanded="false">ddmenu<span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></button>
                                <ul></ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        ';
        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }
}
