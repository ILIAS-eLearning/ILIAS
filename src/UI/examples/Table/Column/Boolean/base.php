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

    $columns = [
        'b1' => $f->table()->column()->boolean('yes/no', 'yes', 'no'),
        'b2' => $f->table()->column()->boolean("0/1", "1", "0"),
        'b3' => $f->table()->column()->boolean(
            "glyph",
            $r->render($f->symbol()->glyph()->up()),
            $r->render($f->symbol()->glyph()->down()->withHighlight())
        )
    ];

    $table = $f->table()->data('Boolean Columns', 50)->withColumns($columns);

    $dummy_records = [2, 13, 4, 5, 16, 17];

    $data_retrieval = new class ($f, $r, $dummy_records) extends T\DataRetrieval {
        public function __construct(
            \ILIAS\UI\Factory $ui_factory,
            \ILIAS\UI\Renderer $ui_renderer,
            array $dummy_records
        ) {
            $this->ui_factory = $ui_factory;
            $this->ui_renderer = $ui_renderer;
            $this->records = $dummy_records;
        }

        public function getRows(
            I\RowFactory $row_factory,
            Range $range,
            Order $order,
            array $visible_column_ids,
            array $additional_parameters
        ): \Generator {
            foreach ($this->records as $number) {
                $row_id = '';
                $record['b1'] = $number > 10;
                $record['b2'] = $record['b1'];
                $record['b3'] = $record['b1'];
                yield $row_factory->standard($row_id, $record);
            }
        }
    };

    return $r->render($table->withData($data_retrieval));
}
