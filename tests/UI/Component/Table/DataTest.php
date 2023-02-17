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
use ILIAS\UI\Implementation\Component as I;
use ILIAS\Data;

/**
 * Tests for the Data Table.
 */
class DataTest extends ILIAS_UI_TestBase
{
    protected function getFactory()
    {
        return new I\Table\Factory(
            new I\SignalGenerator()
        );
    }

    public function testDataTableBasicConstruction()
    {
        $number_of_rows = 12;
        $table = $this->getFactory()->data('title', $number_of_rows);
        $this->assertEquals($number_of_rows, $table->getNumberOfRows());
    }

    public function testDataTableColumns()
    {
        $table = $this->getFactory()->data('title');
        $f = $this->getFactory()->column();
        $table = $table->withColumns([
            'f0' => $f->text("col1"),
            'f1' => $f->text("col2")
        ]);
        $this->assertEquals(2, $table->getColumnCount());
        $check = [
            'f0' => $f->text("col1")->withIndex(0),
            'f1' => $f->text("col2")->withIndex(1)
        ];

        $this->assertEquals($check, $table->getColumns());
        $this->assertEquals($check, $table->getFilteredColumns());
    }

    public function testDataTableActions()
    {
        $table = $this->getFactory()->data('title');
        $f = $this->getFactory()->action();
        $target = $this->getDataFactory()->uri('http://wwww.ilias.de?ref_id=1');
        $actions = [
            $f->standard('act0', 'p', $target),
            $f->single('act1', 'p', $target),
            $f->multi('act2', 'p', $target)
        ];
        $table = $table->withActions($actions);
        $this->assertEquals($actions, $table->getActions());
        $this->assertEqualsCanonicalizing([$actions[0], $actions[2]], $table->getMultiActions());
        $this->assertEqualsCanonicalizing([$actions[0], $actions[1]], $table->getSingleActions());
    }

    public function testDataTableData()
    {
        $table = $this->getFactory()->data('title');
        $data = new class () extends I\Table\DataRetrieval {
            public function getRows(
                Component\Table\RowFactory $row_factory,
                ILIAS\Data\Range $range,
                ILIAS\Data\Order $order,
                array $visible_column_ids,
                array $additional_parameters
            ): \Generator {
                foreach ($this->records as $row_id => $record) {
                    yield $row_factory->standard($row_id, $record);
                }
            }
        };

        $table = $table->withData($data);
        $this->assertEquals($data, $table->getData());
    }
}
