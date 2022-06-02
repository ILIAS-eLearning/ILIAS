<?php declare(strict_types=1);

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
 * Test on Bar Chart implementation.
 */
class ChartBarTest extends ILIAS_UI_TestBase
{
    protected function getFactory() : C\Chart\Bar\Factory
    {
        return new I\Component\Chart\Bar\Factory();
    }

    protected function getDataFactory() : ILIAS\Data\Factory
    {
        return new ILIAS\Data\Factory();
    }

    public function getUIFactory() : NoUIFactory
    {
        return new class extends NoUIFactory {
            public function listing() : I\Component\Listing\Factory
            {
                return new I\Component\Listing\Factory();
            }
        };
    }

    protected function getSimpleDataset() : \ILIAS\Data\Chart\Dataset
    {
        $df = $this->getDataFactory();

        $c_dimension = $df->dimension()->cardinal();

        $dataset = $df->dataset(["Dataset" => $c_dimension]);
        $dataset = $dataset->withPoint("Item", ["Dataset" => 0]);

        return $dataset;
    }

    protected function getExtendedDataset() : \ILIAS\Data\Chart\Dataset
    {
        $df = $this->getDataFactory();

        $c_dimension = $df->dimension()->cardinal();
        $t_dimension = $df->dimension()->range($c_dimension);

        $dataset = $df->dataset(["Dataset 1" => $c_dimension, "Dataset 2" => $t_dimension]);
        $dataset = $dataset->withPoint("Item 1", ["Dataset 1" => -1.25, "Dataset 2" => [-2, -1]]);
        $dataset = $dataset->withPoint("Item 2", ["Dataset 1" => null, "Dataset 2" => [0, 0.5]]);

        return $dataset;
    }

    public function test_implements_factory_interface() : void
    {
        $f = $this->getFactory();

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Chart\\Bar\\Factory", $f);
    }

    public function test_get_instances() : void
    {
        $f = $this->getFactory();

        $dataset = $this->getSimpleDataset();

        $horizontal = $f->horizontal(
            "Horizontal Bar",
            $dataset
        );

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Chart\\Bar\\Bar", $horizontal);
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Chart\\Bar\\Horizontal", $horizontal);

        $vertical = $f->vertical(
            "Vertical Bar",
            $dataset
        );
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Chart\\Bar\\Bar", $vertical);
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Chart\\Bar\\Vertical", $vertical);
    }

    public function test_empty_dataset() : void
    {
        $f = $this->getFactory();
        $df = $this->getDataFactory();

        $c_dimension = $df->dimension()->cardinal();

        $dataset = $df->dataset(["Dataset" => $c_dimension]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("Dataset must not be empty.");
        $horizontal = $f->horizontal(
            "Horizontal Bar",
            $dataset
        );
    }

    /*
    public function test_invalid_dataset_dimension() : void
    {
        $f = $this->getFactory();
        $df = $this->getDataFactory();

        $o_dimension = $df->dimension()->ordinal();

        $dataset = $df->dataset(["Dataset" => $o_dimension]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Expected parameter to be a CardinalDimension or RangeDimension.");
        $horizontal = $f->horizontal(
            "Horizontal Bar",
            $dataset
        );
    }
    */

    public function test_with_title() : void
    {
        $f = $this->getFactory();

        $dataset = $this->getSimpleDataset();

        $horizontal = $f->horizontal(
            "Horizontal Bar",
            $dataset
        );
        $horizontal1 = $horizontal->withTitle("Alternative title for Horizontal Bar");

        $this->assertEquals("Horizontal Bar", $horizontal->getTitle());
        $this->assertEquals("Alternative title for Horizontal Bar", $horizontal1->getTitle());
    }

    public function test_with_title_invisible() : void
    {
        $f = $this->getFactory();

        $dataset = $this->getSimpleDataset();

        $horizontal = $f->horizontal(
            "Horizontal Bar",
            $dataset
        );
        $horizontal1 = $horizontal->withTitleVisible(false);

        $this->assertEquals(true, $horizontal->isTitleVisible());
        $this->assertEquals(false, $horizontal1->isTitleVisible());
    }

    public function test_with_tooltips_invisible() : void
    {
        $f = $this->getFactory();

        $dataset = $this->getSimpleDataset();

        $horizontal = $f->horizontal(
            "Horizontal Bar",
            $dataset
        );
        $horizontal1 = $horizontal->withTooltipsVisible(false);

        $this->assertEquals(true, $horizontal->isTooltipsVisible());
        $this->assertEquals(false, $horizontal1->isTooltipsVisible());
    }

    public function test_with_legend_invisible() : void
    {
        $f = $this->getFactory();

        $dataset = $this->getSimpleDataset();

        $horizontal = $f->horizontal(
            "Horizontal Bar",
            $dataset
        );
        $horizontal1 = $horizontal->withLegendVisible(false);

        $this->assertEquals(true, $horizontal->isLegendVisible());
        $this->assertEquals(false, $horizontal1->isLegendVisible());
    }

    public function test_with_legend_position() : void
    {
        $f = $this->getFactory();

        $dataset = $this->getSimpleDataset();

        $horizontal = $f->horizontal(
            "Horizontal Bar",
            $dataset
        );
        $horizontal1 = $horizontal->withLegendPosition("left");

        $this->assertEquals("top", $horizontal->getLegendPosition());
        $this->assertEquals("left", $horizontal1->getLegendPosition());
    }

    public function test_with_invalid_legend_position() : void
    {
        $f = $this->getFactory();

        $dataset = $this->getSimpleDataset();

        $horizontal = $f->horizontal(
            "Horizontal Bar",
            $dataset
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Position must be 'bottom', 'top', 'left' or 'right'.");

        $horizontal = $horizontal->withLegendPosition("middle");
    }

    public function test_with_dataset() : void
    {
        $f = $this->getFactory();

        $s_dataset = $this->getSimpleDataset();
        $e_dataset = $this->getExtendedDataset();

        $horizontal = $f->horizontal(
            "Horizontal Bar",
            $s_dataset
        );
        $horizontal1 = $horizontal->withDataset($e_dataset);

        $this->assertEquals($s_dataset, $horizontal->getDataset());
        $this->assertEquals($e_dataset, $horizontal1->getDataset());
    }

    public function test_with_bar_configs() : void
    {
        $f = $this->getFactory();
        $df = $this->getDataFactory();

        $dataset = $this->getSimpleDataset();

        $bc = new C\Chart\Bar\BarConfig();
        $bc = $bc->withColor($df->color("#d38000"));

        $bars = [
            "Dataset" => $bc,
        ];

        $horizontal = $f->horizontal(
            "Horizontal Bar",
            $dataset
        );
        $horizontal1 = $horizontal->withBarConfigs($bars);

        $this->assertEquals([], $horizontal->getBarConfigs());
        $this->assertEquals($bars, $horizontal1->getBarConfigs());
    }

    public function test_index_axis() : void
    {
        $f = $this->getFactory();
        $df = $this->getDataFactory();

        $dataset = $this->getSimpleDataset();

        $horizontal = $f->horizontal(
            "Horizontal Bar",
            $dataset
        );

        $this->assertEquals("y", $horizontal->getIndexAxis());

        $vertical = $f->vertical(
            "Vertical Bar",
            $dataset
        );

        $this->assertEquals("x", $vertical->getIndexAxis());
    }

    public function test_render_horizontal() : void
    {
        $r = $this->getDefaultRenderer();
        $f = $this->getFactory();

        $dataset = $this->getSimpleDataset();

        $horizontal = $f->horizontal(
            "bar123",
            $dataset
        );

        $html = $r->render($horizontal);

        $expected_html = <<<EOT
<div class="il-chart-bar-horizontal">
    <canvas id="id_1" height="250px" aria-label="bar123" role="img"></canvas>
</div>
<div class="sr-only">
    <dl>
        <dt>Dataset</dt>
        <dd>
            <ul>
                <li>Item: 0</li>
            </ul>
        </dd>
    </dl>
</div>
EOT;

        $this->assertHTMLEquals("<div>" . $expected_html . "</div>", "<div>" . $html . "</div>");
    }

    public function test_render_vertical() : void
    {
        $r = $this->getDefaultRenderer();
        $f = $this->getFactory();

        $dataset = $this->getExtendedDataset();

        $vertical = $f->vertical(
            "bar123",
            $dataset
        );

        $html = $r->render($vertical);

        $expected_html = <<<EOT
<div class="il-chart-bar-vertical">
    <canvas id="id_1" height="315px" aria-label="bar123" role="img"></canvas>
</div>
<div class="sr-only">
    <dl>
        <dt>Dataset 1</dt>
        <dd>
            <ul>
                <li>Item 1: -1.25</li>
                <li>Item 2: -</li>
            </ul>
        </dd>
        <dt>Dataset 2</dt>
        <dd>
            <ul>
                <li>Item 1: -2 - -1</li>
                <li>Item 2: 0 - 0.5</li>
            </ul>
        </dd>
    </dl>
</div>
EOT;

        $this->assertHTMLEquals("<div>" . $expected_html . "</div>", "<div>" . $html . "</div>");
    }
}
