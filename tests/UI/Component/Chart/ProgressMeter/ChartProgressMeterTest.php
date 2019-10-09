<?php

/* Copyright (c) 2017 Ralph Dittrich <dittrich@qualitus.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation as I;

/**
 * Test on ProgressMeter implementation.
 */
class ChartProgressMeterTest extends ILIAS_UI_TestBase
{
    protected function getFactory()
    {
        return new I\Component\Chart\ProgressMeter\Factory();
    }

    public function test_implements_factory_interface()
    {
        $progressmeter = $this->getFactory();

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Chart\\ProgressMeter\\Factory", $progressmeter);
    }

    public function test_get_instances()
    {
        $progressmeter = $this->getFactory();

        $standard = $progressmeter->standard(400, 250);
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Chart\\ProgressMeter\\Standard", $standard);

        $fixedSize = $progressmeter->fixedSize(400, 250);
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Chart\\ProgressMeter\\FixedSize", $fixedSize);

        $mini = $progressmeter->mini(400, 250);
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Chart\\ProgressMeter\\Mini", $mini);
    }

    public function test_get_values_of_standard()
    {
        $f = $this->getFactory();
        $standard = $f->standard(400, 250, 300, 200);

        $this->assertEquals($standard->getMaximum(), 400);
        $this->assertEquals($standard->getMainValue(), 250);
        $this->assertEquals($standard->getMainValueAsPercent(), 63);
        $this->assertEquals($standard->getRequired(), 300);
        $this->assertEquals($standard->getRequiredAsPercent(), 75);
        $this->assertEquals($standard->getComparison(), 200);
        $this->assertEquals($standard->getComparisonAsPercent(), 50);
    }

    public function test_get_values_of_fixedSize()
    {
        $f = $this->getFactory();
        $fixedSize = $f->fixedSize(400, 250, 300, 200);

        $this->assertEquals($fixedSize->getMaximum(), 400);
        $this->assertEquals($fixedSize->getMainValue(), 250);
        $this->assertEquals($fixedSize->getMainValueAsPercent(), 63);
        $this->assertEquals($fixedSize->getRequired(), 300);
        $this->assertEquals($fixedSize->getRequiredAsPercent(), 75);
        $this->assertEquals($fixedSize->getComparison(), 200);
        $this->assertEquals($fixedSize->getComparisonAsPercent(), 50);
    }

    public function test_get_values_of_mini()
    {
        $f = $this->getFactory();
        $mini = $f->mini(400, 250, 300);

        $this->assertEquals($mini->getMaximum(), 400);
        $this->assertEquals($mini->getMainValue(), 250);
        $this->assertEquals($mini->getMainValueAsPercent(), 63);
        $this->assertEquals($mini->getRequired(), 300);
        $this->assertEquals($mini->getRequiredAsPercent(), 75);
    }

    public function test_render_standard_two_bar()
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

    public function test_render_fixedSize_one_bar()
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

    public function test_render_mini()
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
