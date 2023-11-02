<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Table\Column\EMail;

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
        't1' => $f->table()->column()->email("mail")
    ];

    $data_retrieval = new class () implements I\DataRetrieval {
        protected array $records = [
                ['t1' => 'somebody@example.com'],
                ['t1' => 'somebody_else@example.com']
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

    $table = $f->table()->data('eMail Columns', $columns, $data_retrieval)
        ->withRequest($DIC->http()->request());
    return $r->render($table);
}
