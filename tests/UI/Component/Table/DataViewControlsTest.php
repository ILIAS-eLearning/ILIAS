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
require_once(__DIR__ . "/TableTestBase.php");

use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Component as C;
use ILIAS\UI\Component as I;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\URLBuilder;

/**
 * Tests for the Data Table.
 */
class DataViewControlsTest extends TableTestBase
{
    protected function getDataRetrieval(int $total_count): I\Table\DataRetrieval
    {
        return new class ($total_count) implements I\Table\DataRetrieval {
            protected int $total_count;
            public function __construct(
                int $total_count
            ) {
                $this->total_count = $total_count;
            }
            public function getRows(
                I\Table\DataRowBuilder $row_builder,
                array $visible_column_ids,
                Range $range,
                Order $order,
                ?array $filter_data,
                ?array $additional_parameters
            ): \Generator {
                yield $row_builder->buildStandardRow('', []);
            }
            public function getTotalRowCount(
                ?array $filter_data,
                ?array $additional_parameters
            ): ?int {
                return $this->total_count;
            }
        };
    }

    protected function getTable(int $total_count, array $columns): array
    {
        $factory = $this->getTableFactory();
        $table = $factory->data('Table', $columns, $this->getDataRetrieval($total_count));
        return $table->applyViewControls([], []);
    }

    public function testDataTableHasViewControls(): void
    {
        $factory = $this->getTableFactory();
        $columns = [
            'f1' => $factory->column()->text('f1'),
            'f2' => $factory->column()->text('f2')->withIsOptional(true),
        ];
        $total_count = 12;
        list($table, $view_controls) = $this->getTable($total_count, $columns);

        $this->assertInstanceOf(I\Input\Container\ViewControl\ViewControl::class, $view_controls);
        $this->assertEquals(
            [
                C\Table\Data::VIEWCONTROL_KEY_PAGINATION,
                C\Table\Data::VIEWCONTROL_KEY_ORDERING,
                C\Table\Data::VIEWCONTROL_KEY_FIELDSELECTION,
            ],
            array_keys($view_controls->getInputs())
        );
        foreach (array_values($view_controls->getInputs()) as $vc) {
            $this->assertInstanceOf(I\Input\Container\ViewControl\ViewControlInput::class, $vc);
        }
    }

    public function testDataTableHasNoPaginationViewControl(): void
    {
        $factory = $this->getTableFactory();
        $columns = [
            'f1' => $factory->column()->text('f1'),
            'f2' => $factory->column()->text('f2')->withIsOptional(true),
        ];
        $total_count = current(C\Input\ViewControl\Pagination::DEFAULT_LIMITS) - 1;
        list($table, $view_controls) = $this->getTable($total_count, $columns);

        $this->assertEquals(
            [
                C\Table\Data::VIEWCONTROL_KEY_ORDERING,
                C\Table\Data::VIEWCONTROL_KEY_FIELDSELECTION,
            ],
            array_keys($view_controls->getInputs())
        );
    }

    public function testDataTableHasNoOrderingViewControl(): void
    {
        $factory = $this->getTableFactory();
        $columns = [
            'f1' => $factory->column()->text('f1')
                ->withIsSortable(false),
            'f2' => $factory->column()->text('f2')
                ->withIsSortable(false)
                ->withIsOptional(true),
        ];
        $total_count = 200;
        list($table, $view_controls) = $this->getTable($total_count, $columns);

        $this->assertEquals(
            [
                C\Table\Data::VIEWCONTROL_KEY_PAGINATION,
                C\Table\Data::VIEWCONTROL_KEY_FIELDSELECTION,
            ],
            array_keys($view_controls->getInputs())
        );
    }

    public function testDataTableHasNoFieldSelectionViewControl(): void
    {
        $factory = $this->getTableFactory();
        $columns = [
            'f1' => $factory->column()->text('f1'),
            'f2' => $factory->column()->text('f2'),
        ];
        $total_count = 200;
        list($table, $view_controls) = $this->getTable($total_count, $columns);

        $this->assertEquals(
            [
                C\Table\Data::VIEWCONTROL_KEY_PAGINATION,
                C\Table\Data::VIEWCONTROL_KEY_ORDERING,
            ],
            array_keys($view_controls->getInputs())
        );
    }


    private function getRequestMock(array $returns): ServerRequestInterface
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->method("getUri")
            ->willReturn(new class () {
                public function __toString()
                {
                    return 'http://localhost:80';
                }
            });
        $request
            ->method("getQueryParams")
            ->willReturn($returns);
        return $request;
    }

    public function testDataTableViewControlStorage(): void
    {
        $factory = $this->getTableFactory();
        $columns = [
            'f1' => $factory->column()->text('f1')->withIsOptional(true),
            'f2' => $factory->column()->text('f2')->withIsOptional(true),
            'f3' => $factory->column()->text('f3')->withIsOptional(true),
        ];
        $total_count = 12;
        list($base_table, $view_controls) = $this->getTable($total_count, $columns);

        $table_id = 'testing_data_table';
        $table = $base_table
            ->withId($table_id)
            ->withRequest(
                $this->getRequestMock([
                    'view_control/input_0/input_1' => 0,
                    'view_control/input_0/input_2' => 10,
                    'view_control/input_3/input_4' => 'f2',
                    'view_control/input_3/input_5' => 'DESC',
                    'view_control/input_6' => ['f2']
                ])
            );
        list($table, $view_controls) = $table->applyViewControls([], []);
        //applied values from viewcontrols
        $this->assertEquals(new Range(0, 10), $table->getRange());
        $this->assertEquals(new Order('f2', Order::DESC), $table->getOrder());
        $this->assertEquals(1, count($table->getSelectedOptionalColumns()));

        //default_values for different id
        $table = $base_table
            ->withId('other id')
            ->withRequest($this->getRequestMock([]));
        list($table, $view_controls) = $table->applyViewControls([], []);
        $this->assertEquals(new Range(0, 12), $table->getRange());
        $this->assertEquals(new Order('f1', Order::ASC), $table->getOrder());
        $this->assertEquals(3, count($table->getSelectedOptionalColumns()));

        //applied values from session with empty request
        $table = $base_table
            ->withId($table_id)
            ->withRequest($this->getRequestMock([]));
        list($table, $view_controls) = $table->applyViewControls([], []);
        $this->assertEquals(new Range(0, 10), $table->getRange());
        $this->assertEquals(new Order('f2', Order::DESC), $table->getOrder());
        $this->assertEquals(1, count($table->getSelectedOptionalColumns()));
    }
}
