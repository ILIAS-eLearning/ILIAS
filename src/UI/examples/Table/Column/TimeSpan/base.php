<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Table\Column\TimeSpan;

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

    $columns = [
        'd1' => $f->table()->column()->timeSpan("German Long", $df->dateFormat()->germanLong()),
        'd2' => $f->table()->column()->timeSpan("German Short", $df->dateFormat()->germanShort())
    ];

    $data_retrieval = new class () extends T\DataRetrieval {
        public function getRows(
            I\RowFactory $row_factory,
            array $visible_column_ids,
            Range $range,
            Order $order,
            ?array $filter_data,
            ?array $additional_parameters
        ): \Generator {
            $row_id = '';
            $dat = new \DateTimeImmutable();
            $dat2 = $dat->add(new \DateInterval('PT10H30S'));
            $record = [
                'd1' => [$dat, $dat2],
                'd2' => [$dat, $dat2],
            ];
            yield $row_factory->standard($row_id, $record);
        }
    };

    $table = $f->table()->data('TimeSpan Columns', $columns, $data_retrieval);
    return $r->render($table);
}
