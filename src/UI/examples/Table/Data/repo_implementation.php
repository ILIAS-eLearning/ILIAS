<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Table\Data;

use ILIAS\UI\Implementation\Component\Table as T;
use ILIAS\UI\Component\Table as I;
use ILIAS\Data\Range;
use ILIAS\Data\Order;

function repo_implementation()
{
    /**
     * A Table is prone to reflect database tables, or, better repository entries.
     * Usually, changes in the available data and their representation go along
     * with each other, so it might be a good idea to keep that together.
     *
     * Here is an example, in which the DataRetrieval extends the repository in
     * which the UI-code becomes _very_ small for the actual representation.
     *
     * Please note that the pagination is missing due to an amount of records
     * smaller than the lowest option "number of rows".
    */

    global $DIC;
    $r = $DIC['ui.renderer'];

    $repo = new DataTableDemoRepo();
    $table = $repo->getTableForRepresentation();

    return $r->render(
        $table->withRequest($DIC->http()->request())
    );
}

class DataTableDemoRepo implements I\DataRetrieval
{
    protected \ILIAS\UI\Factory $ui_factory;
    protected \ILIAS\Data\Factory $df;
    protected \ILIAS\Data\DateFormat\DateFormat $current_user_date_format;

    public function __construct()
    {
        global $DIC;
        $this->ui_factory = $DIC['ui.factory'];
        $this->df = new \ILIAS\Data\Factory();
        $this->current_user_date_format = $this->df->dateFormat()->withTime24(
            $DIC['ilUser']->getDateFormat()
        );
    }

    //the repo is capable of building its table-view (similar to forms from a repo)
    public function getTableForRepresentation(): \ILIAS\UI\Implementation\Component\Table\Data
    {
        return $this->ui_factory->table()->data(
            'a data table from a repository',
            $this->getColumsForRepresentation(),
            $this
        );
    }

    //implementation of DataRetrieval - accept params and yield rows
    public function getRows(
        I\DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): \Generator {
        $icons = [
            $this->ui_factory->symbol()->icon()->custom('templates/default/images/standard/icon_checked.svg', '', 'small'),
            $this->ui_factory->symbol()->icon()->custom('templates/default/images/standard/icon_unchecked.svg', '', 'small')
        ];
        foreach ($this->doSelect($order, $range) as $idx => $record) {
            $row_id = (string) $record['usr_id'];
            $record['achieve_txt'] = $record['achieve'] > 80 ? 'passed' : 'failed';
            $record['failure_txt'] = "not " . $record["achieve_txt"];
            $record['repeat'] = $record['achieve'] < 80;
            $record['achieve_icon'] = $icons[(int) $record['achieve'] > 80];
            yield $row_builder->buildDataRow($row_id, $record);
        }
    }

    public function getTotalRowCount(
        ?array $filter_data,
        ?array $additional_parameters
    ): ?int {
        return count($this->dummyrecords());
    }

    //do the actual reading - note, that e.g. order and range are easily converted to SQL
    protected function doSelect(Order $order, Range $range): array
    {
        $sql_order_part = $order->join('ORDER BY', fn(...$o) => implode(' ', $o));
        $sql_range_part = sprintf('LIMIT %2$s OFFSET %1$s', ...$range->unpack());
        return array_map(
            fn($rec) => array_merge($rec, ['sql_order' => $sql_order_part, 'sql_range' => $sql_range_part]),
            $this->dummyrecords()
        );
    }

    //this is how the UI-Table looks - and that's usually quite close to the db-table
    protected function getColumsForRepresentation(): array
    {
        $f = $this->ui_factory;
        return  [
            'usr_id' => $f->table()->column()->number("User ID")
                ->withIsSortable(false),
            'login' => $f->table()->column()->text("Login")
                ->withHighlight(true),
            'email' => $f->table()->column()->eMail("eMail"),
            'last' => $f->table()->column()->date("last login", $this->current_user_date_format),
            'achieve' => $f->table()->column()->status("progress")
                ->withIsOptional(true),
            'achieve_txt' => $f->table()->column()->status("success")
                ->withIsSortable(false)
                ->withIsOptional(true),
            'failure_txt' => $f->table()->column()->status("failure")
                ->withIsSortable(false)
                ->withIsOptional(true, false),
            'achieve_icon' => $f->table()->column()->statusIcon("achieved")
                ->withIsOptional(true),
            'repeat' => $f->table()->column()->boolean("repeat", 'yes', 'no')
                ->withIsSortable(false),
            'fee' => $f->table()->column()->number("Fee")
                ->withDecimals(2)
                ->withUnit('£', I\Column\Number::UNIT_POSITION_FORE),
            'sql_order' => $f->table()->column()->text("sql order part")
                ->withIsSortable(false)
                ->withIsOptional(true),
            'sql_range' => $f->table()->column()->text("sql range part")
                ->withIsSortable(false)
                ->withIsOptional(true)
        ];
    }

    protected function dummyrecords()
    {
        return [
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
    }
}
