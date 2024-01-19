<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Table\Column\Number;

use ILIAS\UI\Implementation\Component\Table as T;
use ILIAS\UI\Component\Table as I;
use ILIAS\Data\Range;
use ILIAS\Data\Order;

function base()
{
    global $DIC;
    $f = $DIC['ui.factory'];
    $r = $DIC['ui.renderer'];

    $dummy_records = [123, 45.66, 78.9876];

    $columns = [
        'n1' => $f->table()->column()->number("some number"),
        'n2' => $f->table()->column()->number("with decimals")
            ->withDecimals(2),
        'n3' => $f->table()->column()->number("with unit before")
            ->withUnit('€', I\Column\Number::UNIT_POSITION_FORE),
        'n4' => $f->table()->column()->number("with unit after")
            ->withDecimals(2)
            ->withUnit('Eur', I\Column\Number::UNIT_POSITION_AFT),
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
            foreach ($this->records as $number) {
                $row_id = '';
                for ($i = 1; $i < 5; $i++) {
                    $record['n' . $i] = $number;
                }
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

    $table = $f->table()->data('Number Columns', $columns, $data_retrieval)
        ->withRequest($DIC->http()->request());
    return $r->render($table);
}
