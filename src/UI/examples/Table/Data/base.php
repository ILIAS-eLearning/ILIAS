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

namespace ILIAS\UI\examples\Table\Data;

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

    // This is what the table will look like
    $columns = [
        'usr_id' => $f->table()->column()->number("User ID")
            ->withIsSortable(false),
        'login' => $f->table()->column()->text("Login")
            ->withHighlight(true),
        'email' => $f->table()->column()->eMail("eMail"),
        'last' => $f->table()->column()->date("last login", $df->dateFormat()->germanLong()),
        'achieve' => $f->table()->column()->statusIcon("progress"),
        'achieve_txt' => $f->table()->column()->status("success")
            ->withIsSortable(false),
        'repeat' => $f->table()->column()->boolean("repeat", 'yes', 'no')
            ->withIsSortable(false),
        'fee' => $f->table()->column()->number("Fee")
            ->withDecimals(2)
            ->withUnit('£', I\Column\Number::UNIT_POSITION_FORE)
    ];

    //setup the table
    $table = $f->table()->data('a data table', $columns, 50);

    // retrieve data and map records to table rows
    $data_retrieval = new class ($f, $r) extends T\DataRetrieval {
        public function __construct(
            \ILIAS\UI\Factory $ui_factory,
            \ILIAS\UI\Renderer $ui_renderer
        ) {
            $this->ui_factory = $ui_factory;
            $this->ui_renderer = $ui_renderer;
        }

        public function getRows(
            I\RowFactory $row_factory,
            array $visible_column_ids,
            Range $range,
            Order $order,
            ?array $filter_data,
            ?array $additional_parameters
        ): \Generator {
            $records = $this->getRecords($order);
            foreach ($records as $idx => $record) {
                $row_id = '';
                $record['achieve_txt'] = $record['achieve'] > 80 ? 'passed' : 'failed';
                $record['repeat'] = $record['achieve'] < 80;
                $record['achieve'] = $this->ui_renderer->render(
                    $this->ui_factory->chart()->progressMeter()->mini(80, $record['achieve'])
                );
                yield $row_factory->standard($row_id, $record);
            }
        }

        protected function getRecords(Order $order): array
        {
            $records =  [
                ['usr_id' => 123,'login' => 'superuser','email' => 'user@example.com',
                 'last' => new \DateTimeImmutable(),'achieve' => 20,'fee' => 0
                ],
                ['usr_id' => 867,'login' => 'student1','email' => 'student1@example.com',
                 'last' => new \DateTimeImmutable(),'achieve' => 90,'fee' => 40
                ],
                ['usr_id' => 8923,'login' => 'student2','email' => 'student2@example.com',
                 'last' => new \DateTimeImmutable(),'achieve' => 66,'fee' => 36.789
                ],
                ['usr_id' => 8748,'login' => 'student3_longname','email' => 'student3_long_email@example.com',
                 'last' => new \DateTimeImmutable(),'achieve' => 66,'fee' => 36.789
                ]
            ];

            list($order_field, $order_direction) = $order->join([], fn ($ret, $key, $value) => [$key, $value]);
            usort($records, fn ($a, $b) => $a[$order_field] <=> $b[$order_field]);
            if ($order_direction === 'DESC') {
                $records = array_reverse($records);
            }

            return $records;
        }
    };

    return $r->render(
        $table
            ->withData($data_retrieval)
            ->withRequest($DIC->http()->request())
    );
}
