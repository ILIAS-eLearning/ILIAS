<?php

/* Copyright (c) 2017 Ralph Dittrich <dittrich@qualitus.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use \ILIAS\UI\Component as C;

/**
 * Test on Gauge implementation.
 */
class GaugeTest extends ILIAS_UI_TestBase
{
    public function test_implements_factory_interface()
    {
        $f = new \ILIAS\UI\Implementation\Factory();

        $this->assertInstanceOf("ILIAS\\UI\\Factory", $f);
        $gauge = $f->chart()->gauge();
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Chart\\Gauge\\Factory", $gauge);
    }

    public function test_get_instances()
    {
        $f = new \ILIAS\UI\Implementation\Factory();
        $gauge = $f->chart()->gauge();

        $standard = $gauge->standard(400, 250);
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Chart\\Gauge\\Standard", $standard);

        $fixedSize = $gauge->fixedSize(400, 250);
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Chart\\Gauge\\FixedSize", $fixedSize);

        $mini = $gauge->mini(400, 250);
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Chart\\Gauge\\Mini", $mini);
    }

    public function test_get_values_of_standard()
    {
        $f = new \ILIAS\UI\Implementation\Factory();
        $standard = $f->chart()->gauge()->standard(400, 250, 300, 200);

        $this->assertEquals($standard->getMaximum(), 400);
        $this->assertEquals($standard->getMainValue(), 250);
        $this->assertEquals($standard->getMainValueAsPercent(), 63);
        $this->assertEquals($standard->getRequired(), 300);
        $this->assertEquals($standard->getRequiredAsPercent(), 75);
        $this->assertEquals($standard->getComparision(), 200);
        $this->assertEquals($standard->getComparisionAsPercent(), 50);
    }

    public function test_get_values_of_fixedSize()
    {
        $f = new \ILIAS\UI\Implementation\Factory();
        $fixedSize = $f->chart()->gauge()->fixedSize(400, 250, 300, 200);

        $this->assertEquals($fixedSize->getMaximum(), 400);
        $this->assertEquals($fixedSize->getMainValue(), 250);
        $this->assertEquals($fixedSize->getMainValueAsPercent(), 63);
        $this->assertEquals($fixedSize->getRequired(), 300);
        $this->assertEquals($fixedSize->getRequiredAsPercent(), 75);
        $this->assertEquals($fixedSize->getComparision(), 200);
        $this->assertEquals($fixedSize->getComparisionAsPercent(), 50);
    }

    public function test_get_values_of_mini()
    {
        $f = new \ILIAS\UI\Implementation\Factory();
        $mini = $f->chart()->gauge()->mini(400, 250, 300);

        $this->assertEquals($mini->getMaximum(), 400);
        $this->assertEquals($mini->getMainValue(), 250);
        $this->assertEquals($mini->getMainValueAsPercent(), 63);
        $this->assertEquals($mini->getRequired(), 300);
        $this->assertEquals($mini->getRequiredAsPercent(), 75);
    }

    public function test_render_standard_two_bar()
    {
        $r = $this->getDefaultRenderer();
        $f = new \ILIAS\UI\Implementation\Factory();
        $standard = $f->chart()->gauge()->standard(400, 250, 300, 200);

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Chart\\Gauge\\Standard", $standard);

        $html = $r->render($standard);

        $expected_html =
            '<div class="il-chart-gauge-box il-chart-gauge-responsive">' .
            '  <div class="il-chart-gauge-container">' .
            '    <div class="il-chart-gauge-marker-box" style="transform: rotate(57.5deg)">' .
            '      <div class="il-chart-gauge-marker"><div class="il-chart-gauge-arrow"></div></div>' .
            '    </div>' .
            '    <div class="il-chart-gauge-outerbox   il-chart-gauge-bar-no-success  ">' .
            '      <div class="il-chart-gauge-pointer pointer-1" style="transform: rotate(-25deg) skew(41.6349deg)"></div>' .
            '      <div class="il-chart-gauge-pointer pointer-2" style="transform: rotate(22.3651deg) skew(41.6349deg)"></div>' .
            '      <div class="il-chart-gauge-pointer pointer-3" style="transform: rotate(69.7302deg) skew(41.6349deg)"></div>' .
            '      <div class="il-chart-gauge-innerbox    ">' .
            '        <div class="il-chart-gauge-pointer pointer-1" style="transform: rotate(-25deg) skew(51.615deg)"></div>' .
            '        <div class="il-chart-gauge-pointer pointer-2" style="transform: rotate(12.385deg) skew(51.615deg)"></div>' .
            '        <div class="il-chart-gauge-pointer pointer-3" style="transform: rotate(49.77deg) skew(51.615deg)"></div>' .
            '        <div class="il-chart-gauge-cover">' .
            '          <div class="il-chart-gauge-text-container">' .
            '            <span class="il-chart-gauge-score-text"></span>' .
            '            <span class="il-chart-gauge-score">63 %</span>' .
            '            <span class="il-chart-gauge-minscore">75 %</span>' .
            '            <span class="il-chart-gauge-minscore-text"></span>' .
            '            <span class="il-chart-gauge-test-score">50 %</span>' .
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
        $f = new \ILIAS\UI\Implementation\Factory();
        $fixedSize = $f->chart()->gauge()->fixedSize(400, 250, 300);

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Chart\\Gauge\\FixedSize", $fixedSize);

        $html = $r->render($fixedSize);

        $expected_html =
            '<div class="il-chart-gauge-box il-chart-gauge-fixed-size">' .
            '  <div class="il-chart-gauge-container">' .
            '    <div class="il-chart-gauge-marker-box" style="transform: rotate(57.5deg)">' .
            '      <div class="il-chart-gauge-marker"><div class="il-chart-gauge-arrow"></div></div>' .
            '    </div>' .
            '    <div class="il-chart-gauge-outerbox   il-chart-gauge-bar-no-success  ">' .
            '      <div class="il-chart-gauge-pointer pointer-1" style="transform: rotate(-25deg) skew(41.6349deg)"></div>' .
            '      <div class="il-chart-gauge-pointer pointer-2" style="transform: rotate(22.3651deg) skew(41.6349deg)"></div>' .
            '      <div class="il-chart-gauge-pointer pointer-3" style="transform: rotate(69.7302deg) skew(41.6349deg)"></div>' .
            '      <div class="il-chart-gauge-cover">' .
            '        <div class="il-chart-gauge-text-container">' .
            '          <span class="il-chart-gauge-score-text"></span>' .
            '          <span class="il-chart-gauge-score">63 %</span>' .
            '          <span class="il-chart-gauge-minscore">75 %</span>' .
            '          <span class="il-chart-gauge-minscore-text"></span>' .
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
        $f = new \ILIAS\UI\Implementation\Factory();
        $mini = $f->chart()->gauge()->mini(400, 250, 300);

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Chart\\Gauge\\Mini", $mini);

        $html = $r->render($mini);

        $expected_html =
            '<div class="il-chart-gauge-box il-chart-gauge-mini">' .
            '  <div class="il-chart-gauge-mini-container">' .
            '    <div class="il-chart-gauge-marker-box" style="transform: rotate(57.5deg)">' .
            '      <div class="il-chart-gauge-mini-marker"></div>' .
            '    </div>' .
            '    <div class="il-chart-gauge-outerbox   il-chart-gauge-bar-no-success  ">' .
            '      <div class="il-chart-gauge-pointer pointer-1" style="transform: rotate(-25deg) skew(41.6349deg)"></div>' .
            '      <div class="il-chart-gauge-pointer pointer-2" style="transform: rotate(22.3651deg) skew(41.6349deg)"></div>' .
            '      <div class="il-chart-gauge-pointer pointer-3" style="transform: rotate(69.7302deg) skew(41.6349deg)"></div>' .
            '      <div class="il-chart-gauge-cover">' .
            '        <div class="il-chart-gauge-text-container"></div>' .
            '      </div>' .
            '    </div>' .
            '  </div>' .
            '</div>';

        $this->assertHTMLEquals($expected_html, $html);
    }
}