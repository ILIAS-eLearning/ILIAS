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
            foreach ($this->records as $number) {
                $row_id = '';
                for ($i=1; $i<5; $i++) {
                    $record['n' . $i] = $number;
                }
                yield $row_factory->standard($row_id, $record);
            }
        }
    };

    $table = $f->table()->data('Number Columns', $columns, $data_retrieval);
    return $r->render($table);
}
