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

namespace ILIAS\UI\examples\Table\Column\Breadcrumb;

use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Component\Table\DataRetrieval;

/**
 * ---
 * description: >
 *   A table with a breadcrumb column.
 *
 * expected output: >
 *   ILIAS shows a table with one column and one row.
 *   The row consists of 2 clickable links separated by simple arrows (>).
 * ---
 */
function base()
{
    global $DIC;

    $data_retrieval = new class () implements DataRetrieval {
        public function getRows(
            DataRowBuilder $row_builder,
            array $visible_column_ids,
            Range $range,
            Order $order,
            ?array $filter_data,
            ?array $additional_parameters
        ): \Generator {
            global $DIC;
            yield $row_builder->buildDataRow('dummy', [
                'object' => $DIC->ui()->factory()->breadcrumbs([
                    $DIC->ui()->factory()->link()->standard('Repository', '/'),
                    $DIC->ui()->factory()->link()->standard('Course', '/login.php'),
                ]),
            ]);
        }

        public function getTotalRowCount(
            ?array $filter_data,
            ?array $additional_parameters
        ): ?int {
            return 1;
        }
    };

    $columns = [
        'object' => $DIC->ui()->factory()->table()->column()->breadcrumb('Object'),
    ];

    $table = $DIC->ui()->factory()->table()->data($data_retrieval, 'Test Breadcrumb Column', $columns)
        ->withRequest($DIC->http()->request());

    return $DIC->ui()->renderer()->render($table);
}
