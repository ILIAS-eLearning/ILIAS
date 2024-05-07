<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Table\Column\LinkListing;

use ILIAS\UI\Component\Table as I;
use ILIAS\Data\Range;
use ILIAS\Data\Order;

function base(): string
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $r = $DIC->ui()->renderer();

    $columns = [
        'l1' => $f->table()->column()->linkListing("a link list column")
    ];

    $some_link = $f->link()->standard('ILIAS Homepage', 'http://www.ilias.de');
    $some_linklisting = $f->listing()->unordered([$some_link, $some_link, $some_link]);

    $dummy_records = [
        ['l1' => $some_linklisting],
        ['l1' => $some_linklisting]
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

    $table = $f->table()->data('Link List Columns', $columns, $data_retrieval)
               ->withRequest($DIC->http()->request());
    return $r->render($table);
}
