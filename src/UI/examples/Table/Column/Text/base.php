<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Table\Column\Text;

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
        't1' => $f->table()->column()->text("some text")
    ];

    $dummy_records = [
        ['t1' => 'this is some text'],
        ['t1' => 'this is some other text']
    ];

    $data_retrieval = new class ($dummy_records) extends T\DataRetrieval {
        public function __construct(array $dummy_records)
        {
            $this->records = $dummy_records;
        }

        public function getRows(
            I\RowFactory $row_factory,
            array $visible_column_ids,
            Range $range,
            Order $order,
            ?array $filter_data,
            ?array $additional_parameters
        ): \Generator {
            foreach ($this->records as $idx => $record) {
                $row_id = '';
                yield $row_factory->standard($row_id, $record);
            }
        }
    };

    $table = $f->table()->data('Text Columns', $columns, $data_retrieval);
    return $r->render($table);
}
