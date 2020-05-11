<?php

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

    // define actions
    $modal = getSomeExampleModal($f);
    $signal = $modal->getShowSignal();

    $actions = [
        'edit' => $f->table()->action()->single('Edit', 'ids', $signal),
        'delete' => $f->table()->action()->standard('Delete', 'ids', buildDemoURL('table_action=delete')),
        'compare' => $f->table()->action()->multi('Compare', 'ids', $signal)
    ];

    // retrieve data and map records to table rows
    $data_retrieval = new class($dummy_records) extends T\DataRetrieval {
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
            foreach ($this->records as $idx => $record) {
                //identify record (e.g. for actions)
                $row_id = 'rowid-' . (string) $idx;

                //maybe do something with the record
                $record['f4'] = $record['f3'] * 2;

                //decide on availability of actions
                $not_to_be_edited = $record['f3'] > 4;
                $not_to_be_deleted = $record['f3'] > 4 && $record['f3'] < 5;

                //and yield the row
                yield $row_factory->standard($row_id, $record)
                    ->withDisabledAction('edit', $not_to_be_edited)
                    ->withDisabledAction('delete', $not_to_be_deleted);
            }
        }
    };

    //setup the table
    $table = $f->table()->data('a data table', 50)
        ->withColumns($columns)
        ->withActions($actions)
        ->withData($data_retrieval);

    //apply request and render
    $request = $DIC->http()->request();
    $out = [
        $modal,
        $table->withRequest($request)
    ];

    //demo results
    $params = [];
    parse_str($request->getUri()->getQuery(), $params);
    if (array_key_exists('table_action', $params)) {
        $items = [
            'table_action' => $params['table_action'],
            'ids' => print_r($params['ids'], true)
        ];

        $out[] = $f->divider()->horizontal();
        $out[] = $f->listing()->characteristicValue()->text($items);
    }

    return $r->render($out);
}

function buildDemoURL($param)
{
    $df = new \ILIAS\Data\Factory();
    $url = $df->uri(
        $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME']
        . ':' . $_SERVER['SERVER_PORT']
        . $_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING']
        . '&' . $param
    );
    return $url;
}

function getSomeExampleModal($factory)
{
    $image = $factory->image()->responsive("src/UI/examples/Image/mountains.jpg", "Image source: https://stocksnap.io, Creative Commons CC0 license");
    $page = $factory->modal()->lightboxImagePage($image, 'Mountains');
    $modal = $factory->modal()->lightbox($page);
    return $modal;
}
