<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Table\Column\Link;

use ILIAS\UI\Implementation\Component\Table as T;
use ILIAS\UI\Component\Table as I;
use ILIAS\Data\Range;
use ILIAS\Data\Order;

function base(): string
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $r = $DIC->ui()->renderer();

    $columns = [
        'l1' => $f->table()->column()->link("a link column")
    ];

    $dummy_records = [
        ['l1' => $f->link()->standard('ILIAS Homepage', 'http://www.ilias.de')],
        ['l1' => $f->link()->standard('ILIAS Homepage', 'http://www.ilias.de')],

    ];

    $data_retrieval = new class ($dummy_records) implements I\DataRetrieval {
        protected array $records;

        public function __construct(array $dummy_records)
        {
            $this->records = $dummy_records;
        }

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

    $table = $f->table()->data('Link Columns', $columns, $data_retrieval)
        ->withRequest($DIC->http()->request());
    return $r->render($table);
}
