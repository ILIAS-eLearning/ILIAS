<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Table\Action\Multi;

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
    $here_uri = $df->uri(
        $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME']
        . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI']
    );


    //define multi actions for the table
    $actions = [
        'some_action' => $f->table()->action()->multi(
            'do this',
            'relay_param',
            $here_uri->withParameter('demo_table_action', 'do_something')
        ),
        'some_other_action' => $f->table()->action()->multi(
            'do something else',
            'relay_param',
            $here_uri->withParameter('demo_table_action', 'do_something_else')
        ),
    ];

    $table = getExampleTable($f)
        ->withActions($actions);


    //render table and results
    $result = [$table];
    $params = [];
    $request = $DIC->http()->request();
    parse_str($request->getUri()->getQuery(), $params);
    if (array_key_exists('demo_table_action', $params)) {
        $items = [
            'table_action' => $params['demo_table_action'],
            'id' => print_r($params['relay_param'] ?? '', true),
        ];
        $result[] = $f->divider()->horizontal();
        $result[] = $f->listing()->characteristicValue()->text($items);
    }
    return $r->render($result);
}

function getExampleTable($f)
{
    $columns = ['f1' => $f->table()->column()->text("Field 1")];

    $data_retrieval = new class () extends T\DataRetrieval {
        public function getRows(
            I\RowFactory $row_factory,
            array $visible_column_ids,
            Range $range,
            Order $order,
            ?array $filter_data,
            ?array $additional_parameters
        ): \Generator {
            foreach (range(0, 5) as $cnt) {
                yield $row_factory->standard('row_id' . $cnt, ['f1' => $cnt]);
            }
        }
    };
    return $f->table()->data('a data table with actions', $columns, $data_retrieval);
}
