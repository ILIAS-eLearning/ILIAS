<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Table\Column\Date;

use ILIAS\UI\Implementation\Component\Table as T;
use ILIAS\UI\Component\Table as I;
use ILIAS\Data\Range;
use ILIAS\Data\Order;

function base()
{
    global $DIC;
    $f = $DIC['ui.factory'];
    $r = $DIC['ui.renderer'];
    $df = new \ILIAS\Data\Factory();
    $current_user = $DIC['ilUser'];

    $columns = [
        'd1' => $f->table()->column()->date("German Long", $df->dateFormat()->germanLong()),
        'd2' => $f->table()->column()->date("Time Only", $df->dateFormat()->custom()->hours24()->colon()->minutes()->get()),
        'd3' => $f->table()->column()->date("User Preference", $current_user->getDateFormat()),
    ];

    $data_retrieval = new class () implements I\DataRetrieval {
        public function getRows(
            I\DataRowBuilder $row_builder,
            array $visible_column_ids,
            Range $range,
            Order $order,
            ?array $filter_data,
            ?array $additional_parameters
        ): \Generator {
            $row_id = '';
            $dat = new \DateTimeImmutable();
            $record = [
                'd1' => $dat,
                'd2' => $dat,
                'd3' => $dat
            ];
            yield $row_builder->buildDataRow($row_id, $record);
        }

        public function getTotalRowCount(
            ?array $filter_data,
            ?array $additional_parameters
        ): ?int {
            return 1;
        }
    };

    $table = $f->table()->data('Date Columns', $columns, $data_retrieval)
        ->withRequest($DIC->http()->request());
    return $r->render($table);
}
