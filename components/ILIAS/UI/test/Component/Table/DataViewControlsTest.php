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

require_once("vendor/composer/vendor/autoload.php");
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
            public function __construct(
                protected int $total_count
            ) {
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
}
