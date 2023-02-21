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
        'usr_id' => $f->table()->column()->number("User ID"),
        'login' => $f->table()->column()->text("Login")
            ->withHighlight(true),
        'email' => $f->table()->column()->email("eMail"),
        'last' => $f->table()->column()->date("last login", $df->dateFormat()->germanLong()),
        'achieve' => $f->table()->column()->statusIcon(""),
        'achieve_txt' => $f->table()->column()->status("progress"),
        'repeat' => $f->table()->column()->boolean("repeat", 'yes', 'no'),
    ];

    //setup the table
    $table = $f->table()->data('a data table', 50)
        ->withColumns($columns);


    //this is some dummy-data:
    $dummy_records = [
        [
            'usr_id' => 123,
            'login' => 'superuser',
            'email' => 'user@example.com',
            'last' => new \DateTimeImmutable(),
            'achieve' => 20
        ],
        [
            'usr_id' => 867,
            'login' => 'student1',
            'email' => 'stdudent1@example.com',
            'last' => new \DateTimeImmutable(),
            'achieve' => 90
        ],
        [
            'usr_id' => 8923,
            'login' => 'student2',
            'email' => 'stdudent2@example.com',
            'last' => new \DateTimeImmutable(),
            'achieve' => 66
        ],

    ];

    // retrieve data and map records to table rows
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
            foreach ($this->records as $idx => $record) {
                $row_id = '';
                $record['achieve_txt'] = $record['achieve'] > 80 ? 'passed' : 'failed';
                $record['repeat'] = $record['achieve'] < 80;
                $record['achieve'] = $this->ui_renderer->render(
                    $this->ui_factory->chart()->progressMeter()->mini(80, $record['achieve'])
                );
                yield $row_factory->standard($row_id, $record);
            }
        }
    };

    return $r->render($table->withData($data_retrieval));
}
