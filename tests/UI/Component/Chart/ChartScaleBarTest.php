<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation as I;

/**
 * Test scale bar charts.
 */
class ChartScaleBarTest extends ILIAS_UI_TestBase
{
    protected function getFactory()
    {
        return new I\Component\Chart\Factory(
            $this->createMock(C\Chart\ProgressMeter\Factory::class)
        );
    }

    public function test_implements_factory_interface()
    {
        $f = $this->getFactory();

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Chart\\ScaleBar", $f->scaleBar(array("1" => false)));
    }

    public function test_get_items()
    {
        $f = $this->getFactory();

        $items = array(
            "None" => false,
            "Low" => false,
            "Medium" => true,
            "High" => false
        );

        $c = $f->scaleBar($items);

        $this->assertEquals($c->getItems(), $items);
    }

    public function test_render()
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $items = array(
            "None" => false,
            "Low" => false,
            "Medium" => true,
            "High" => false
        );

        $c = $f->scaleBar($items);

        $html = $r->render($c);

        $expected_html = <<<EOT
<ul class="il-chart-scale-bar">
	<li style="width:25%">
		<div class="il-chart-scale-bar-item ">
			None 
		</div>
	</li>
	<li style="width:25%">
		<div class="il-chart-scale-bar-item ">
			Low 
		</div>
	</li>
	<li style="width:25%">
		<div class="il-chart-scale-bar-item il-chart-scale-bar-active">
			Medium <span class="sr-only">(active)</span>
		</div>
	</li>
	<li style="width:25%">
		<div class="il-chart-scale-bar-item ">
			High 
		</div>
	</li>
</ul>
EOT;

        $this->assertHTMLEquals($expected_html, $html);
    }
}
