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

    $columns = [
        'd1' => $f->table()->column()->date("German Long", $df->dateFormat()->germanLong()),
        'd2' => $f->table()->column()->date("German Short", $df->dateFormat()->germanShort())
    ];
    $table = $f->table()->data('the date column', 50)->withColumns($columns);

    $data_retrieval = new class () extends T\DataRetrieval {
        public function getRows(
            I\RowFactory $row_factory,
            Range $range,
            Order $order,
            array $visible_column_ids,
            array $additional_parameters
        ): \Generator {
            $row_id = '';
            $dat = new \DateTimeImmutable();
            $record = [
                'd1' => $dat,
                'd2' => $dat
            ];
            yield $row_factory->standard($row_id, $record);
        }
    };

    return $r->render($table->withData($data_retrieval));
}
