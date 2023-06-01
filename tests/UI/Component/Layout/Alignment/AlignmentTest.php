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

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "../../../../Base.php");

use ILIAS\UI\Implementation\Component as C;
use ILIAS\UI\Component as I;

/**
 * Tests for the Alignment Layout
 */
class AlignmentTest extends ILIAS_UI_TestBase
{
    public function testAlignmentBasicConstruction(): void
    {
        $f = new C\Layout\Alignment\Factory();
        $blocks = [
            new C\Legacy\Legacy('block', new C\SignalGenerator()),
            new C\Legacy\Legacy('block', new C\SignalGenerator())
        ];
        $ph = $f->preferHorizontal($blocks);
        $this->assertInstanceOf(I\Layout\Alignment\Alignment::class, $ph);
        $this->assertEquals([$blocks], $ph->getBlocksets());
        $fh = $f->forceHorizontal($blocks);
        $this->assertInstanceOf(I\Layout\Alignment\Alignment::class, $fh);
        $this->assertEquals([$blocks], $fh->getBlocksets());
    }

    public function testAlignmentRendering(): void
    {
        $f = new C\Layout\Alignment\Factory();
        $blocks = [
            new C\Legacy\Legacy('block', new C\SignalGenerator()),
            new C\Legacy\Legacy('block', new C\SignalGenerator())
        ];
        $renderer = $this->getDefaultRenderer();

        $ph = $f->preferHorizontal($blocks);

        $actual = $this->brutallyTrimHTML($renderer->render($ph));
        $expected = $this->brutallyTrimHTML('
            <div class="c-layout-alignment c-layout-alignment--horizontal">
                <div class=c-layout-alignment__group>
                    <div class=c-layout-alignment__block>block</div>
                    <div class=c-layout-alignment__block>block</div>
                </div>
            </div>
        ');
        $this->assertEquals($expected, $actual);

        $fh = $f->forceHorizontal($blocks);
        $actual = $this->brutallyTrimHTML($renderer->render($fh));
        $expected = $this->brutallyTrimHTML('
            <div class="c-layout-alignment c-layout-alignment--horizontal  c-layout-alignment--nowrap">
                <div class=c-layout-alignment__group>
                    <div class=c-layout-alignment__block>block</div>
                    <div class=c-layout-alignment__block>block</div>
                </div>
            </div>
        ');

        $this->assertEquals($expected, $actual);
    }
}
