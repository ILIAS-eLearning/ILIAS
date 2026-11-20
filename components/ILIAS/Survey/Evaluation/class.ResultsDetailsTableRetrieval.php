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

namespace ILIAS\Survey\Evaluation;

use Generator;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;

/**
 * Provides the rows of the grid of a single question
 * (Results > Details) for a KS data table.
 *
 * The rows keep the order of the grid, i.e. the order of the
 * answer categories / matrix rows, which is meaningful.
 */
class ResultsDetailsTableRetrieval implements DataRetrieval
{
    /**
     * @param array<int, array<int, mixed>> $grid_rows positional cells as returned by getGrid()["rows"]
     */
    public function __construct(
        protected array $grid_rows
    ) {
    }

    public static function getColumnId(int $index): string
    {
        return "col_" . $index;
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): Generator {
        $rows = array_slice(
            $this->grid_rows,
            $range->getStart(),
            $range->getLength(),
            true
        );
        foreach ($rows as $row_index => $cells) {
            $record = [];
            foreach (array_values($cells) as $index => $cell) {
                $record[self::getColumnId($index)] = htmlspecialchars(
                    trim((string) ($cell ?? "")),
                    ENT_QUOTES,
                    "UTF-8",
                    false
                );
            }
            yield $row_builder->buildDataRow((string) $row_index, $record);
        }
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count($this->grid_rows);
    }
}
