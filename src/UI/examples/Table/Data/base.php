<?php declare(strict_types=1);

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

    //this is some dummy-data:
    $dummy_records = [
        ['f1' => 'value1.1','f2' => 'value1.2','f3' => 1.11],
        ['f1' => 'value2.1','f2' => 'value2.2','f3' => 2.22],
        ['f1' => 'value3.1','f2' => 'value3.2','f3' => 4.44],
        ['f1' => 'value4.1','f2' => 'value4.2','f3' => 8.88]
    ];

    // This is what the table will look like
    $columns = [
        'f1' => $f->table()->column()->text("Field 1")
            ->withIsSortable(false),

        'f0' => $f->table()->column()->text("empty"),

        'f2' => $f->table()->column()->text("Field 2")
            ->withIsOptional(true)
            ->withIsInitiallyVisible(false),

        'f3' => $f->table()->column()->number("Field 3")
            ->withIsOptional(true)
            ->withDecimals(2),

        'f4' => $f->table()->column()->number("Field 4")
            ->withIsOptional(false)
    ];

    // retrieve data and map records to table rows
    $data_retrieval = new class($dummy_records) extends T\DataRetrieval {
        protected $records;

        public function __construct(array $dummy_records)
        {
            $this->records = $dummy_records;
        }

        public function getRows(
            I\RowFactory $row_factory,
            Range $range,
            Order $order,
            array $visible_column_ids,
            array $additional_parameters
        ) : \Generator {
            foreach ($this->records as $record) {
                //maybe do something with the record
                $record['f4'] = $record['f3'] * 2;
                //and yield the row
                yield $row_factory->map($record);
            }
        }
    };

    //setup the table
    $table = $f->table()->data('a data table', 50)
        ->withColumns($columns)
        ->withData($data_retrieval);

    //apply request and render
    $request = $DIC->http()->request();
    return $r->render($table->withRequest($request));
}
