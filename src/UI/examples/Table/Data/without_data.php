<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Table\Data;

use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use Generator;

/**
 * Example showing a data table without any data and hence no entries, which
 * will automatically display an according message.
 */
function without_data(): string
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

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
            return null;
        }
    };

    $table = $factory->table()->data(
        'Empty Data Table',
        [
            'col1' => $factory->table()->column()->text('Column 1'),
            'col2' => $factory->table()->column()->number('Column 2'),
        ],
        $empty_retrieval
    );

    return $renderer->render($table);
}
