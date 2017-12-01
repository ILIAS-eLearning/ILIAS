<?php

/* Copyright (c) 2017 Ralph Dittrich <dittrich@qualitus.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use \ILIAS\UI\Component as C;

/**
 * Test on Speedo implementation.
 */
class SpeedoTest extends ILIAS_UI_TestBase
{
    public function test_implements_factory_interface()
    {
        $f = new \ILIAS\UI\Implementation\Factory();

        $this->assertInstanceOf("ILIAS\\UI\\Factory", $f);
        $speedo = $f->chart()->speedo();
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Chart\\Speedo\\Factory", $speedo);
    }

    public function test_get_instances()
    {
        $f = new \ILIAS\UI\Implementation\Factory();
        $speedo = $f->chart()->speedo();

        $standard = $speedo->standard(array(
            'goal' => 400,
            'score' => 250,
        ));
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Chart\\Speedo\\Standard", $standard);

        $responsive = $speedo->responsive(array(
            'goal' => 400,
            'score' => 250,
        ));
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Chart\\Speedo\\Responsive", $responsive);

        $mini = $speedo->mini(array(
            'goal' => 400,
            'score' => 250,
        ));
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Chart\\Speedo\\Mini", $mini);
    }

    public function test_get_values_of_standard()
    {
        $f = new \ILIAS\UI\Implementation\Factory();
        $standard = $f->chart()->speedo()->standard(array(
            'goal' => 400,
            'score' => 250,
            'minimum' => 300,
            'diagnostic' => 200,
        ));

        $this->assertEquals($standard->getGoal(), 400);
        $this->assertEquals($standard->getScore(false), 250);
        $this->assertEquals($standard->getScore(true), 63);
        $this->assertEquals($standard->getMinimum(false), 300);
        $this->assertEquals($standard->getMinimum(true), 75);
        $this->assertEquals($standard->getDiagnostic(false), 200);
        $this->assertEquals($standard->getDiagnostic(true), 50);
    }

    public function test_get_values_of_responsive()
    {
        $f = new \ILIAS\UI\Implementation\Factory();
        $responsive = $f->chart()->speedo()->responsive(array(
            'goal' => 400,
            'score' => 250,
            'minimum' => 300,
            'diagnostic' => 200,
        ));

        $this->assertEquals($responsive->getGoal(), 400);
        $this->assertEquals($responsive->getScore(false), 250);
        $this->assertEquals($responsive->getScore(true), 63);
        $this->assertEquals($responsive->getMinimum(false), 300);
        $this->assertEquals($responsive->getMinimum(true), 75);
        $this->assertEquals($responsive->getDiagnostic(false), 200);
        $this->assertEquals($responsive->getDiagnostic(true), 50);
    }

    public function test_get_values_of_mini()
    {
        $f = new \ILIAS\UI\Implementation\Factory();
        $mini = $f->chart()->speedo()->mini(array(
            'goal' => 400,
            'score' => 250,
            'minimum' => 300,
        ));

        $this->assertEquals($mini->getGoal(), 400);
        $this->assertEquals($mini->getScore(false), 250);
        $this->assertEquals($mini->getScore(true), 63);
        $this->assertEquals($mini->getMinimum(false), 300);
        $this->assertEquals($mini->getMinimum(true), 75);
    }

    public function test_render_standard_two_bar()
    {
        $r = $this->getDefaultRenderer();
        $f = new \ILIAS\UI\Implementation\Factory();
        $standard = $f->chart()->speedo()->standard(array(
            'goal' => 400,
            'score' => 250,
            'minimum' => 300,
            'diagnostic' => 200,
        ));

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Chart\\Speedo\\Standard", $standard);

        $html = $r->render($standard);

        $expected_html =
            '<div class="il-chart-speedo-box standard">' .
            '  <div class="il-chart-speedo-container">' .
            '    <div class="il-chart-speedo-marker-box" style="transform: rotate(57.5deg)">' .
            '      <div class="il-chart-speedo-marker"><div class="il-chart-speedo-arrow"></div></div>' .
            '    </div>' .
            '    <div class="il-chart-speedo-outerbox il-chart-speedo-red">' .
            '      <div class="il-chart-speedo-pointer pointer-1" style="transform: rotate(-25deg) skew(41.6349deg)"></div>' .
            '      <div class="il-chart-speedo-pointer pointer-2" style="transform: rotate(23.3651deg) skew(41.6349deg)"></div>' .
            '      <div class="il-chart-speedo-pointer pointer-3" style="transform: rotate(71.7302deg) skew(41.6349deg)"></div>' .
            '      <div class="il-chart-speedo-innerbox ">' .
            '        <div class="il-chart-speedo-pointer pointer-1" style="transform: rotate(-25deg) skew(51.615deg)"></div>' .
            '        <div class="il-chart-speedo-pointer pointer-2" style="transform: rotate(13.385deg) skew(51.615deg)"></div>' .
            '        <div class="il-chart-speedo-pointer pointer-3" style="transform: rotate(51.77deg) skew(51.615deg)"></div>' .
            '        <div class="il-chart-speedo-cover">' .
            '          <div class="il-chart-speedo-text-container">' .
            '            <span class="il-chart-speedo-score-text"></span>' .
            '            <span class="il-chart-speedo-score">63 %</span>' .
            '            <span class="il-chart-speedo-minscore">75 %</span>' .
            '            <span class="il-chart-speedo-minscore-text"></span>' .
            '            <span class="il-chart-speedo-test-score">50 %</span>' .
            '          </div>' .
            '        </div>' .
            '      </div>' .
            '    </div>' .
            '  </div>' .
            '</div>';

        $this->assertHTMLEquals($expected_html, $html);
    }

    public function test_render_responsive_one_bar()
    {
        $r = $this->getDefaultRenderer();
        $f = new \ILIAS\UI\Implementation\Factory();
        $responsive = $f->chart()->speedo()->responsive(array(
            'goal' => 400,
            'score' => 250,
            'minimum' => 300,
        ));

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Chart\\Speedo\\Responsive", $responsive);

        $html = $r->render($responsive);

        $expected_html =
            '<div class="il-chart-speedo-box responsive">' .
            '  <div class="il-chart-speedo-container">' .
            '    <div class="il-chart-speedo-marker-box" style="transform: rotate(57.5deg)">' .
            '      <div class="il-chart-speedo-marker"><div class="il-chart-speedo-arrow"></div></div>' .
            '    </div>' .
            '    <div class="il-chart-speedo-outerbox il-chart-speedo-red">' .
            '      <div class="il-chart-speedo-pointer pointer-1" style="transform: rotate(-25deg) skew(41.6349deg)"></div>' .
            '      <div class="il-chart-speedo-pointer pointer-2" style="transform: rotate(23.3651deg) skew(41.6349deg)"></div>' .
            '      <div class="il-chart-speedo-pointer pointer-3" style="transform: rotate(71.7302deg) skew(41.6349deg)"></div>' .
            '      <div class="il-chart-speedo-cover">' .
            '        <div class="il-chart-speedo-text-container">' .
            '          <span class="il-chart-speedo-score-text"></span>' .
            '          <span class="il-chart-speedo-score">63 %</span>' .
            '          <span class="il-chart-speedo-minscore">75 %</span>' .
            '          <span class="il-chart-speedo-minscore-text"></span>' .
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
        $mini = $f->chart()->speedo()->mini(array(
            'goal' => 400,
            'score' => 250,
            'minimum' => 300,
        ));

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Chart\\Speedo\\Mini", $mini);

        $html = $r->render($mini);

        $expected_html =
            '<div class="il-chart-speedo-box mini">' .
            '  <div class="il-chart-speedo-mini-container">' .
            '    <div class="il-chart-speedo-outerbox il-chart-speedo-red">' .
            '      <div class="il-chart-speedo-pointer pointer-1" style="transform: rotate(-25deg) skew(41.6349deg)"></div>' .
            '      <div class="il-chart-speedo-pointer pointer-2" style="transform: rotate(23.3651deg) skew(41.6349deg)"></div>' .
            '      <div class="il-chart-speedo-pointer pointer-3" style="transform: rotate(71.7302deg) skew(41.6349deg)"></div>' .
            '      <div class="il-chart-speedo-cover">' .
            '        <div class="il-chart-speedo-text-container"></div>' .
            '      </div>' .
            '    </div>' .
            '  </div>' .
            '</div>';

        $this->assertHTMLEquals($expected_html, $html);
    }
}