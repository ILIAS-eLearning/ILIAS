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

require_once("vendor/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\MainControls\Slate\Drilldown;

/**
 * Tests for the DrilldownSlate.
 */
class DrilldownSlateTest extends ILIAS_UI_TestBase
{
    public function getUIFactory(): NoUIFactory
    {
        return new class () extends NoUIFactory {
            protected function getSigGen()
            {
                return new I\SignalGenerator();
            }

            public function menu(): C\Menu\Factory
            {
                return new I\Menu\Factory(
                    $this->getSigGen(),
                );
            }

            public function symbol(): C\Symbol\Factory
            {
                return new I\Symbol\Factory(
                    new I\Symbol\Icon\Factory(),
                    new I\Symbol\Glyph\Factory(),
                    new I\Symbol\Avatar\Factory()
                );
            }

            public function mainControls(): C\MainControls\Factory
            {
                $slate_factory = new I\MainControls\Slate\Factory(
                    $this->getSigGen(),
                    new I\Counter\Factory(),
                    $this->symbol()
                );
                return new I\MainControls\Factory($this->getSigGen(), $slate_factory);
            }

            public function button(): C\Button\Factory
            {
                return new I\Button\Factory();
            }
        };
    }

    public function testImplementsFactoryInterface(): Drilldown
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
    public function testRendering(Drilldown $slate): void
    {
        $r = $this->getDefaultRenderer();
        $html = $r->render($slate);

        $expected = '
            <div class="il-maincontrols-slate disengaged" id="id_4">
                <div class="il-maincontrols-slate-content" data-replace-marker="content">
                    <section class="c-drilldown" id="id_2">
                        <header class="c-drilldown__header--showbacknav">
                            <div></div>
                            <div></div>
                            <div class="c-drilldown__filter">
                                <label for=\'id_3\' class="control-label">filter_nodes_in</label>
                                <input id=\'id_3\' type="text" name=\'\' class="form-control" />
                            </div>
                            <div class="c-drilldown__backnav">
                                <button class="btn btn-bulky" id="id_1" aria-label="back">
                                    <span class="glyph" aria-label="collapse/back" role="img">
                                        <span class="glyphicon glyphicon-triangle-left" aria-hidden="true"></span>
                                    </span>
                                    <span class="bulky-label"></span>
                                </button>
                            </div>
                        </header>
                        <div class="c-drilldown__menu">
                            <ul aria-live="polite" aria-label="ddmenu">
                                <li class="c-drilldown__menu--no-items"> drilldown_no_items</li>
                            </ul>
                        </div>
                    </section>
                </div>
            </div>
        ';
        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }
}
