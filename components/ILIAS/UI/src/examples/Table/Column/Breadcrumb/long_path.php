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
 *   A table with a very long breadcrumb path.
 * expected output: >
 *   ILIAS shows a table with two columns and two row.
 *   The first column of each row consists of 8 ckickable links separated by simple arrows (>).
 *   The second column of each row displays the text "Max Mustermann".
 *   The links break into multiple lines if there is no space left to display all links.
 * ---
 */
function long_path()
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
            $link = fn($x) => $DIC->ui()->factory()->link()->standard($x, '/');
            $path = [
                'Repository', 'Faculty', 'Department', 'Institute', 'Professorship',
                'Learning Management Systems', 'Best Practice', 'ILIAS',
            ];
            $row = $row_builder->buildDataRow('dummy', [
                'object' => $DIC->ui()->factory()->breadcrumbs(array_map($link, $path)),
                'owner' => 'Max Mustermann',
            ]);
            yield $row;
            yield $row;
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
        'owner' => $DIC->ui()->factory()->table()->column()->text('Owner'),
    ];

    $table = $DIC->ui()->factory()->table()->data($data_retrieval, 'Test long Breadcrumb Column', $columns)
        ->withRequest($DIC->http()->request());

    return $DIC->ui()->renderer()->render($table);
}
