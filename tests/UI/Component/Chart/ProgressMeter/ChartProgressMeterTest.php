<?php

declare(strict_types=1);

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

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation as I;

/**
 * Test on ProgressMeter implementation.
 */
class ChartProgressMeterTest extends ILIAS_UI_TestBase
{
    protected function getFactory(): C\Chart\ProgressMeter\Factory
    {
        return new I\Component\Chart\ProgressMeter\Factory();
    }

    public function test_implements_factory_interface(): void
    {
        $progressmeter = $this->getFactory();

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Chart\\ProgressMeter\\Factory", $progressmeter);
    }

    public function test_get_instances(): void
    {
        $progressmeter = $this->getFactory();

        $standard = $progressmeter->standard(400, 250);
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Chart\\ProgressMeter\\Standard", $standard);

        $fixedSize = $progressmeter->fixedSize(400, 250);
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Chart\\ProgressMeter\\FixedSize", $fixedSize);

        $mini = $progressmeter->mini(400, 250);
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Chart\\ProgressMeter\\Mini", $mini);
    }

    public function test_get_values_of_standard(): void
    {
        $f = $this->getFactory();
        $standard = $f->standard(400, 250, 300, 200);

        $this->assertEquals(400, $standard->getMaximum());
        $this->assertEquals(250, $standard->getMainValue());
        $this->assertEquals(63, $standard->getMainValueAsPercent());
        $this->assertEquals(300, $standard->getRequired());
        $this->assertEquals(75, $standard->getRequiredAsPercent());
        $this->assertEquals(200, $standard->getComparison());
        $this->assertEquals(50, $standard->getComparisonAsPercent());
    }

    public function test_get_values_of_fixedSize(): void
    {
        $f = $this->getFactory();
        $fixedSize = $f->fixedSize(400, 250, 300, 200);

        $this->assertEquals(400, $fixedSize->getMaximum());
        $this->assertEquals(250, $fixedSize->getMainValue());
        $this->assertEquals(63, $fixedSize->getMainValueAsPercent());
        $this->assertEquals(300, $fixedSize->getRequired());
        $this->assertEquals(75, $fixedSize->getRequiredAsPercent());
        $this->assertEquals(200, $fixedSize->getComparison());
        $this->assertEquals(50, $fixedSize->getComparisonAsPercent());
    }

    public function test_get_values_of_mini(): void
    {
        $f = $this->getFactory();
        $mini = $f->mini(400, 250, 300);

        $this->assertEquals(400, $mini->getMaximum());
        $this->assertEquals(250, $mini->getMainValue());
        $this->assertEquals(63, $mini->getMainValueAsPercent());
        $this->assertEquals(300, $mini->getRequired());
        $this->assertEquals(75, $mini->getRequiredAsPercent());
    }

    public function test_render_standard_two_bar(): void
    {
        $r = $this->getDefaultRenderer();
        $f = $this->getFactory();
        $standard = $f->standard(100, 75, 80, 50);

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Chart\\ProgressMeter\\Standard", $standard);

        $html = $r->render($standard);

        $expected_html =
            '<div class="il-chart-progressmeter-box ">' .
            '<div class="il-chart-progressmeter-container">' .
            '<svg viewBox="0 0 50 40" class="il-chart-progressmeter-viewbox">' .
            '<path class="il-chart-progressmeter-circle-bg" stroke-dasharray="100, 100" ' .
            'd="M10.4646,37.0354 q-5.858,-5.858 -5.858,-14.142 a1,1 0 1,1 40,0 q0,8.284 -5.858,14.142"></path>' .
            '<g class="il-chart-progressmeter-multicircle">' .
            '<path class="il-chart-progressmeter-circle no-success" ' .
            'd="M9.6514,37.8486 q-6.1948,-6.1948 -6.1948,-14.9552 a1,1 0 1,1 42.30,0 q0,8.7604 -6.1948,14.9552" ' .
            'stroke-dasharray="75, 100"></path>' .
            '<path class="il-chart-progressmeter-circle active" ' .
            'd="M11.2778,36.2222 q-5.5212,-5.5212 -5.5212,-13.3288 a1,1 0 1,1 37.70,0 q0,7.8076 -5.5212,13.3288" ' .
            'stroke-dasharray="44.4, 100"></path>' .
            '</g>' .
            '<g class="il-chart-progressmeter-text">' .
            '<text class="text-score-info" x="25" y="16"></text>' .
            '<text class="text-score" x="25" y="25">75 %</text>' .
            '<text class="text-comparision" x="25" y="31">80 %</text>' .
            '<text class="text-comparision-info" x="25" y="34"></text>' .
            '</g>' .
            '<g class="il-chart-progressmeter-needle " style="transform: rotate(82.8deg)">' .
            '<polygon class="il-chart-progressmeter-needle-border" points="23.5,0.1 25,2.3 26.5,0.1"></polygon>' .
            '<polygon class="il-chart-progressmeter-needle-fill" points="23.5,0 25,2.2 26.5,0"></polygon>' .
            '</g>' .
            '</svg>' .
            '</div>' .
            '</div>';

        $this->assertHTMLEquals($expected_html, $html);
    }

    public function test_render_fixedSize_one_bar(): void
    {
        $r = $this->getDefaultRenderer();
        $f = $this->getFactory();
        $fixedSize = $f->fixedSize(100, 75, 80, null, 300);

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Chart\\ProgressMeter\\FixedSize", $fixedSize);

        $html = $r->render($fixedSize);

        $expected_html =
            '<div class="il-chart-progressmeter-box fixed-size">' .
            '<div class="il-chart-progressmeter-container">' .
            '<svg viewBox="0 0 50 40" class="il-chart-progressmeter-viewbox">' .
            '<path class="il-chart-progressmeter-circle-bg" stroke-dasharray="100, 100" ' .
            'd="M10.4646,37.0354 q-5.858,-5.858 -5.858,-14.142 a1,1 0 1,1 40,0 q0,8.284 -5.858,14.142"></path>' .
            '<g class="il-chart-progressmeter-monocircle">' .
            '<path class="il-chart-progressmeter-circle no-success" stroke-dasharray="75, 100" ' .
            'd="M10.4646,37.0354 q-5.858,-5.858 -5.858,-14.142 a1,1 0 1,1 40,0 q0,8.284 -5.858,14.142"></path>' .
            '</g>' .
            '<g class="il-chart-progressmeter-text">' .
            '<text class="text-score-info" x="25" y="16"></text>' .
            '<text class="text-score" x="25" y="25">75 %</text>' .
            '<text class="text-comparision" x="25" y="31">80 %</text>' .
            '<text class="text-comparision-info" x="25" y="34"></text>' .
            '</g>' .
            '<g class="il-chart-progressmeter-needle " style="transform: rotate(82.8deg)">' .
            '<polygon class="il-chart-progressmeter-needle-border" points="23.5,0.1 25,2.3 26.5,0.1"></polygon>' .
            '<polygon class="il-chart-progressmeter-needle-fill" points="23.5,0 25,2.2 26.5,0"></polygon>' .
            '</g>' .
            '</svg>' .
            '</div>' .
            '</div>';

        $this->assertHTMLEquals($expected_html, $html);
    }

    public function test_render_mini(): void
    {
        $r = $this->getDefaultRenderer();
        $f = $this->getFactory();
        $mini = $f->mini(400, 250, 300);

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Chart\\ProgressMeter\\Mini", $mini);

        $html = $r->render($mini);

        $expected_html =
            '<div class="il-chart-progressmeter-box il-chart-progressmeter-mini">' .
            '<div class="il-chart-progressmeter-container">' .
            '<svg viewBox="0 0 50 40" class="il-chart-progressmeter-viewbox">' .
            '<path class="il-chart-progressmeter-circle-bg" stroke-dasharray="100, 100" ' .
            'd="M9,35 q-4.3934,-4.3934 -4.3934,-10.6066 a1,1 0 1,1 40,0 q0,6.2132 -4.3934,10.6066"></path>' .
            '<path class="il-chart-progressmeter-circle no-success" stroke-dasharray="54.495, 100" ' .
            'd="M9,35 q-4.3934,-4.3934 -4.3934,-10.6066 a1,1 0 1,1 40,0 q0,6.2132 -4.3934,10.6066"></path>' .
            '<path class="il-chart-progressmeter-needle " stroke-dasharray="100, 100" d="M25,10 l0,15" ' .
            'style="transform: rotate(57.5deg)"></path>' .
            '</svg>' .
            '</div>' .
            '</div>';

        $this->assertHTMLEquals($expected_html, $html);
    }
}
