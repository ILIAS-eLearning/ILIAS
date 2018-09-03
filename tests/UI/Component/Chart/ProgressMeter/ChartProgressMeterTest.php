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
	protected function getFactory() {
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
        $standard = $f->standard(400, 250, 300, 200);

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Chart\\ProgressMeter\\Standard", $standard);

        $html = $r->render($standard);

        $expected_html =
            '<div class="il-chart-progressmeter-box il-chart-progressmeter-responsive">' .
            '  <div class="il-chart-progressmeter-container">' .
            '    <div class="il-chart-progressmeter-marker-box" style="transform: rotate(57.5deg)">' .
            '      <div class="il-chart-progressmeter-marker"><div class="il-chart-progressmeter-arrow"></div></div>' .
            '    </div>' .
            '    <div class="il-chart-progressmeter-outerbox   il-chart-progressmeter-bar-no-success  ">' .
            '      <div class="il-chart-progressmeter-pointer pointer-1" style="transform: rotate(-25deg) skew(41.6349deg)"></div>' .
            '      <div class="il-chart-progressmeter-pointer pointer-2" style="transform: rotate(22.3651deg) skew(41.6349deg)"></div>' .
            '      <div class="il-chart-progressmeter-pointer pointer-3" style="transform: rotate(69.7302deg) skew(41.6349deg)"></div>' .
            '      <div class="il-chart-progressmeter-innerbox    ">' .
            '        <div class="il-chart-progressmeter-pointer pointer-1" style="transform: rotate(-25deg) skew(51.615deg)"></div>' .
            '        <div class="il-chart-progressmeter-pointer pointer-2" style="transform: rotate(12.385deg) skew(51.615deg)"></div>' .
            '        <div class="il-chart-progressmeter-pointer pointer-3" style="transform: rotate(49.77deg) skew(51.615deg)"></div>' .
            '        <div class="il-chart-progressmeter-cover">' .
            '          <div class="il-chart-progressmeter-text-container">' .
            '            <span class="il-chart-progressmeter-score-text"></span>' .
            '            <span class="il-chart-progressmeter-score">63 %</span>' .
            '            <span class="il-chart-progressmeter-minscore">75 %</span>' .
            '            <span class="il-chart-progressmeter-minscore-text"></span>' .
            '            <span class="il-chart-progressmeter-test-score">50 %</span>' .
            '          </div>' .
            '        </div>' .
            '      </div>' .
            '    </div>' .
            '  </div>' .
            '</div>';

        $this->assertHTMLEquals($expected_html, $html);
    }

    public function test_render_fixedSize_one_bar()
    {
        $r = $this->getDefaultRenderer();
        $f = $this->getFactory();
        $fixedSize = $f->fixedSize(400, 250, 300);

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Chart\\ProgressMeter\\FixedSize", $fixedSize);

        $html = $r->render($fixedSize);

        $expected_html =
            '<div class="il-chart-progressmeter-box il-chart-progressmeter-fixed-size">' .
            '  <div class="il-chart-progressmeter-container">' .
            '    <div class="il-chart-progressmeter-marker-box" style="transform: rotate(57.5deg)">' .
            '      <div class="il-chart-progressmeter-marker"><div class="il-chart-progressmeter-arrow"></div></div>' .
            '    </div>' .
            '    <div class="il-chart-progressmeter-outerbox   il-chart-progressmeter-bar-no-success  ">' .
            '      <div class="il-chart-progressmeter-pointer pointer-1" style="transform: rotate(-25deg) skew(41.6349deg)"></div>' .
            '      <div class="il-chart-progressmeter-pointer pointer-2" style="transform: rotate(22.3651deg) skew(41.6349deg)"></div>' .
            '      <div class="il-chart-progressmeter-pointer pointer-3" style="transform: rotate(69.7302deg) skew(41.6349deg)"></div>' .
            '      <div class="il-chart-progressmeter-cover">' .
            '        <div class="il-chart-progressmeter-text-container">' .
            '          <span class="il-chart-progressmeter-score-text"></span>' .
            '          <span class="il-chart-progressmeter-score">63 %</span>' .
            '          <span class="il-chart-progressmeter-minscore">75 %</span>' .
            '          <span class="il-chart-progressmeter-minscore-text"></span>' .
            '        </div>' .
            '      </div>' .
            '    </div>' .
            '  </div>' .
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
            '  <div class="il-chart-progressmeter-mini-container">' .
            '    <div class="il-chart-progressmeter-marker-box" style="transform: rotate(57.5deg)">' .
            '      <div class="il-chart-progressmeter-mini-marker"></div>' .
            '    </div>' .
            '    <div class="il-chart-progressmeter-outerbox   il-chart-progressmeter-bar-no-success  ">' .
            '      <div class="il-chart-progressmeter-pointer pointer-1" style="transform: rotate(-25deg) skew(41.6349deg)"></div>' .
            '      <div class="il-chart-progressmeter-pointer pointer-2" style="transform: rotate(22.3651deg) skew(41.6349deg)"></div>' .
            '      <div class="il-chart-progressmeter-pointer pointer-3" style="transform: rotate(69.7302deg) skew(41.6349deg)"></div>' .
            '      <div class="il-chart-progressmeter-cover">' .
            '        <div class="il-chart-progressmeter-text-container"></div>' .
            '      </div>' .
            '    </div>' .
            '  </div>' .
            '</div>';

        $this->assertHTMLEquals($expected_html, $html);
    }
}
