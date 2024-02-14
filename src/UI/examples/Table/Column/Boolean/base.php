<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Table\Column\Boolean;

use ILIAS\UI\Implementation\Component\Table as T;
use ILIAS\UI\Component\Table as I;
use ILIAS\Data\Range;
use ILIAS\Data\Order;

function base()
{
    global $DIC;
    $f = $DIC['ui.factory'];
    $r = $DIC['ui.renderer'];

    $dummy_records = [2, 13, 4, 5, 16, 17];

    $columns = [
        'b1' => $f->table()->column()->boolean('yes/no', 'yes', 'no'),
        'b2' => $f->table()->column()->boolean("0/1", "1", "0"),
        'b3' => $f->table()->column()->boolean(
            "icon",
            $f->symbol()->icon()->custom('templates/default/images/standard/icon_checked.svg', '', 'small'),
            $f->symbol()->icon()->custom('templates/default/images/standard/icon_unchecked.svg', '', 'small')
        ),
        'b4' => $f->table()->column()->boolean(
            "glyph",
            $f->symbol()->glyph()->like(),
            $f->symbol()->glyph()->dislike()->withHighlight()
        )
    ];

    $data_retrieval = new class ($dummy_records) implements I\DataRetrieval {
        protected array $records;

        public function __construct(
            array $dummy_records
        ) {
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
                $record['b1'] = $number > 10;
                $record['b2'] = $record['b1'];
                $record['b3'] = $record['b1'];
                $record['b4'] = $record['b1'];
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

    $table = $f->table()->data('Boolean Columns', $columns, $data_retrieval)
        ->withRequest($DIC->http()->request());
    return $r->render($table);
}
