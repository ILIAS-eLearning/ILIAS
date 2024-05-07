<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Table\Column\Status;

use ILIAS\UI\Implementation\Component\Table as T;
use ILIAS\UI\Component\Table as I;
use ILIAS\Data\Range;
use ILIAS\Data\Order;

function base()
{
    global $DIC;
    $f = $DIC['ui.factory'];
    $r = $DIC['ui.renderer'];

    $columns = [
        't1' => $f->table()->column()->status("status")
    ];

    $data_retrieval = new class () implements I\DataRetrieval {
        protected array $records = [
                ['t1' => 'unknown'],
                ['t1' => 'incomplete'],
                ['t1' => 'failed'],
                ['t1' => 'passed'],
                ['t1' => 'completed']
            ];

        public function getRows(
            I\DataRowBuilder $row_builder,
            array $visible_column_ids,
            Range $range,
            Order $order,
            ?array $filter_data,
            ?array $additional_parameters
        ): \Generator {
            foreach ($this->records as $idx => $record) {
                $row_id = '';
                yield $row_builder->buildDataRow($row_id, $record);
            }
        }

        public function getTotalRowCount(
            ?array $filter_data,
            ?array $additional_parameters
        ): ?int {
            return count($this->records);
        }
    };

    $table = $f->table()->data('Status Columns', $columns, $data_retrieval)
        ->withRequest($DIC->http()->request());
    return $r->render($table);
}
