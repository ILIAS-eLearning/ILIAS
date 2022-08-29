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

require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation as I;

/**
 * Test scale bar charts.
 */
class ChartScaleBarTest extends ILIAS_UI_TestBase
{
    protected function getFactory(): C\Chart\Factory
    {
        return new I\Component\Chart\Factory(
            $this->createMock(C\Chart\ProgressMeter\Factory::class),
            $this->createMock(C\Chart\Bar\Factory::class)
        );
    }

    public function test_implements_factory_interface(): void
    {
        $f = $this->getFactory();

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Chart\\ScaleBar", $f->scaleBar(array("1" => false)));
    }

    public function test_get_items(): void
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

    public function test_render(): void
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
