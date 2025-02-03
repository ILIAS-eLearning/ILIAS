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

namespace ILIAS\UI\examples\Table\Data;

use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use Generator;

/**
 * ---
 * description: >
 *   Example showing a data table without any data and hence no entries, which
 *   will automatically display an according message.
 *
 * expected output: >
 *   ILIAS shows the rendered Component.
 * ---
 */
function without_data(): string
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    $empty_retrieval = new class () implements DataRetrieval {
        public function getRows(
            DataRowBuilder $row_builder,
            array $visible_column_ids,
            Range $range,
            Order $order,
            ?array $filter_data,
            ?array $additional_parameters
        ): Generator {
            yield from [];
        }

        public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
        {
            return 0;
        }
    };

    $table = $factory->table()->data(
        'Empty Data Table',
        [
            'col1' => $factory->table()->column()->text('Column 1')
                ->withIsSortable(false),
            'col2' => $factory->table()->column()->number('Column 2')
                ->withIsSortable(false),
        ],
        $empty_retrieval
    );

    return $renderer->render($table->withRequest($request));
}
