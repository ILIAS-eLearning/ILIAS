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
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Component as C;
use ILIAS\UI\Component as I;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Tests for the Data Table.
 */
class DataTest extends ILIAS_UI_TestBase
{
    protected function getFactory()
    {
        return new C\Table\Factory(
            new C\SignalGenerator(),
            new \ILIAS\Data\Factory()
        );
    }

    protected function getDataRetrieval(): I\Table\DataRetrieval
    {
        return new class () extends C\Table\DataRetrieval {
            public function getRows(
                I\Table\RowFactory $row_factory,
                array $visible_column_ids,
                Range $range,
                Order $order,
                ?array $filter_data,
                ?array $additional_parameters
            ): \Generator {
                yield $row_factory->standard('', []);
            }
        };
    }

    public function testDataTableBasicConstruction(): void
    {
        $data = $this->getDataRetrieval();
        $cols = ['f0' => $this->getFactory()->column()->text("col1")];
        $table = $this->getFactory()->data('title', $cols, $data);
        $this->assertEquals(800, $table->getNumberOfRows());
        $this->assertInstanceOf(Order::class, $table->getOrder());
        $this->assertInstanceOf(Range::class, $table->getRange());
        $this->assertInstanceOf(I\Signal::class, $table->getActionSignal());
        $this->assertInstanceOf(I\Signal::class, $table->getSelectionSignal());
        $this->assertFalse($table->hasSingleActions());
        $this->assertFalse($table->hasMultiActions());
        $this->assertEquals($data, $table->getDataRetrieval());
    }

    public function testDataTableConstructionWithErrorColumns(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $data = $this->getDataRetrieval();
        $cols = ['f0' => "col1"];
        $table = $this->getFactory()->data('title', $cols, $data);
    }

    public function testDataTableConstructionWithoutColumns(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $data = $this->getDataRetrieval();
        $cols = [];
        $table = $this->getFactory()->data('title', $cols, $data);
    }

    public function testDataTableColumns(): void
    {
        $f = $this->getFactory()->column();
        $cols = [
            'f0' => $f->text("col1"),
            'f1' => $f->text("col2")
        ];
        $table = $this->getFactory()->data('title', $cols, $this->getDataRetrieval());

        $this->assertEquals(2, $table->getColumnCount());
        $check = [
            'f0' => $f->text("col1")->withIndex(0),
            'f1' => $f->text("col2")->withIndex(1)
        ];
        $this->assertEquals($check, $table->getColumns());
        $this->assertEquals($check, $table->getVisibleColumns());
    }

    public function testDataTableActions(): void
    {
        $f = $this->getFactory()->action();
        $target = $this->getDataFactory()->uri('http://wwww.ilias.de?ref_id=1');
        $actions = [
            $f->standard('act0', 'p', $target),
            $f->single('act1', 'p', $target),
            $f->multi('act2', 'p', $target)
        ];
        $cols = ['f0' => $this->getFactory()->column()->text("col1")];
        $table = $this->getFactory()->data('title', $cols, $this->getDataRetrieval())
            ->withActions($actions);

        $this->assertEquals($actions, $table->getActions());
        $this->assertEqualsCanonicalizing([$actions[0], $actions[2]], $table->getMultiActions());
        $this->assertEqualsCanonicalizing([$actions[0], $actions[1]], $table->getSingleActions());
    }

    protected function getTable(): I\Table\Data
    {
        $data = $this->getDataRetrieval();
        $cols = ['f0' => $this->getFactory()->column()->text("col1")];
        $table = $this->getFactory()->data('title', $cols, $data);
        return $table;
    }

    public function testDataTableWithRequest(): void
    {
        $table = $this->getTable();
        $request = $this->createMock(ServerRequestInterface::class);
        $this->assertEquals($request, $table->withRequest($request)->getRequest());
    }

    public function testDataTableWithNumberOfRows(): void
    {
        $table = $this->getTable();
        $nor = 12;
        $this->assertEquals($nor, $table->withNumberOfRows($nor)->getNumberOfRows());
    }

    public function testDataTableWithOrder(): void
    {
        $table = $this->getTable();
        $order = new Order('aspect', 'DESC');
        $this->assertEquals($order, $table->withOrder($order)->getOrder());
    }

    public function testDataTableWithRange(): void
    {
        $table = $this->getTable();
        $range = new Range(17, 53);
        $this->assertEquals($range, $table->withRange($range)->getRange());
    }

    public function testDataTableWithFilter(): void
    {
        $table = $this->getTable();
        $filter = [
            'aspect' => ['values']
        ];
        $this->assertEquals($filter, $table->withFilter($filter)->getFilter());
    }

    public function testDataTableWithAdditionalParams(): void
    {
        $table = $this->getTable();
        $params = [
            'param' => 'value'
        ];
        $this->assertEquals($params, $table->withAdditionalParameters($params)->getAdditionalParameters());
    }

    public function testDataTableWithSelectedOptionalCols(): void
    {
        $data = $this->getDataRetrieval();
        $cols = [
            'f0' => $this->getFactory()->column()->text(''),
            'f1' => $this->getFactory()->column()->text('')
                ->withIsOptional(true)
                ->withIsInitiallyVisible(false),
            'f2' => $this->getFactory()->column()->text('')
        ];
        $table = $this->getFactory()->data('title', $cols, $data);
        $this->assertEquals(3, $table->getColumnCount());
        $this->assertEquals(['f0', 'f2'], array_keys($table->getVisibleColumns()));
        $this->assertEquals(0, $table->getVisibleColumns()['f0']->getIndex());
        $this->assertEquals(2, $table->getVisibleColumns()['f2']->getIndex());
    }
}
