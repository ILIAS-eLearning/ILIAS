<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\UI\examples\Table\Column\TimeSpan;

use ILIAS\UI\Implementation\Component\Table as T;
use ILIAS\UI\Component\Table as I;
use ILIAS\Data\Range;
use ILIAS\Data\Order;

/**
 * ---
 * expected output: >
 *   ILIAS shows the rendered Component.
 * ---
 */
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
            $dat2 = $dat->add(new \DateInterval('PT10H30S'));
            $record = [
                'd1' => [$dat, $dat2],
                'd2' => [$dat, $dat2],
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

    $table = $f->table()->data('TimeSpan Columns', $columns, $data_retrieval)
        ->withRequest($DIC->http()->request());
    return $r->render($table);
}
