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
        $vert = $f->vertical(...$blocks);
        $this->assertInstanceOf(I\Layout\Alignment\Alignment::class, $vert);
        $this->assertEquals($blocks, $vert->getBlocks());
        $ed = $f->horizontal()->dynamicallyDistributed(...$blocks);
        $this->assertInstanceOf(I\Layout\Alignment\Alignment::class, $ed);
        $this->assertEquals($blocks, $ed->getBlocks());
        $dd = $f->horizontal()->dynamicallyDistributed(...$blocks);
        $this->assertInstanceOf(I\Layout\Alignment\Alignment::class, $dd);
        $this->assertEquals($blocks, $dd->getBlocks());
    }

    public function testAlignmentEvenlyRendering(): void
    {
        $f = new C\Layout\Alignment\Factory();
        $blocks = [
            new C\Legacy\Legacy('block', new C\SignalGenerator()),
            new C\Legacy\Legacy('block', new C\SignalGenerator())
        ];
        $renderer = $this->getDefaultRenderer();

        $ed = $f->horizontal()->evenlyDistributed(...$blocks);

        $actual = $this->brutallyTrimHTML($renderer->render($ed));
        $expected = $this->brutallyTrimHTML('
            <div class="c-layout-alignment c-layout-alignment--horizontal-evenly">
                <div class=c-layout-alignment__block>block</div>
                <div class=c-layout-alignment__block>block</div>
            </div>
        ');
        $this->assertEquals($expected, $actual);
    }

    public function testAlignmentDynamicalRendering(): void
    {
        $f = new C\Layout\Alignment\Factory();
        $blocks = [
            new C\Legacy\Legacy('block', new C\SignalGenerator()),
            new C\Legacy\Legacy('block', new C\SignalGenerator())
        ];
        $renderer = $this->getDefaultRenderer();

        $dd = $f->horizontal()->dynamicallyDistributed(...$blocks);
        $actual = $this->brutallyTrimHTML($renderer->render($dd));
        $expected = $this->brutallyTrimHTML('
            <div class="c-layout-alignment c-layout-alignment--horizontal-dynamically">
                <div class=c-layout-alignment__block>block</div>
                <div class=c-layout-alignment__block>block</div>
            </div>
        ');
        $this->assertEquals($expected, $actual);
    }

    public function testAlignmentVerticalRendering(): void
    {
        $f = new C\Layout\Alignment\Factory();
        $blocks = [
            new C\Legacy\Legacy('block', new C\SignalGenerator()),
            new C\Legacy\Legacy('block', new C\SignalGenerator())
        ];
        $renderer = $this->getDefaultRenderer();

        $vert = $f->vertical(...$blocks);
        $actual = $this->brutallyTrimHTML($renderer->render($vert));
        $expected = $this->brutallyTrimHTML('
            <div class="c-layout-alignment c-layout-alignment--vertical">
                <div class=c-layout-alignment__block>block</div>
                <div class=c-layout-alignment__block>block</div>
            </div>
        ');
        $this->assertEquals($expected, $actual);
    }
}
