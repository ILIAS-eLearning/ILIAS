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

namespace ILIAS\UI\examples\Table\Column\StatusIcon;

use ILIAS\UI\Implementation\Component\Table as T;
use ILIAS\UI\Component\Table as I;
use ILIAS\Data\Range;
use ILIAS\Data\Order;

function base()
{
    global $DIC;
    $f = $DIC['ui.factory'];
    $r = $DIC['ui.renderer'];

    $dummy_records = [23, 45, 67];

    $columns = [
        'i1' => $f->table()->column()->statusIcon("icon"),
        'i2' => $f->table()->column()->statusIcon("chart")
    ];

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
            array $visible_column_ids,
            Range $range,
            Order $order,
            ?array $filter_data,
            ?array $additional_parameters
        ): \Generator {
            foreach ($this->records as $number) {
                $row_id = '';
                $record['i1'] = $this->ui_renderer->render(
                    $this->ui_factory->symbol()->icon()->standard('crs', '', 'small')
                );
                $record['i2'] = $this->ui_renderer->render(
                    $this->ui_factory->chart()->progressMeter()->mini(80, $number)
                );
                yield $row_factory->standard($row_id, $record);
            }
        }
    };

    $table = $f->table()->data('StatusIcons Columns', $columns, $data_retrieval);
    return $r->render($table);
}
