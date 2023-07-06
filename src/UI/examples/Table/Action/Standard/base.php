<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Table\Action\Standard;

use ILIAS\UI\Implementation\Component\Table as T;
use ILIAS\UI\Implementation\Component\Table\Action\Action;
use ILIAS\UI\Component\Table as I;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\UI\URLBuilder;

function base()
{
    global $DIC;
    $f = $DIC['ui.factory'];
    $r = $DIC['ui.renderer'];
    $df = new \ILIAS\Data\Factory();

    $query_params_namespace = ['datatable', '1'];

    $here_uri = $df->uri($DIC->http()->request()->getUri()->__toString());
    $url_builder = new URLBuilder($here_uri);

    //define standard (both single and multi) actions for the table
    $actions = [
        'some_action' => $f->table()->action()->standard(
            'do this',
            ...array_values(
                $url_builder->withURI(
                    $here_uri->withParameter( // this is a fix parameter
                        'demo_table_action',  // the action's parameter-name
                        'do_something'        // its value
                    )
                )->acquireParameter(
                    $query_params_namespace,
                    Action::ROW_ID_PARAMETER
                )
            ),
        ),
        'some_other_action' => $f->table()->action()->standard(
            'do something else',
            ...array_values(
                $url_builder->withURI(
                    $here_uri->withParameter( //this is a fix parameter
                        'demo_table_action',
                        'do_something_else'
                    )
                )->acquireParameter( //this is the builder-token for row_ids
                    $query_params_namespace,
                    T\Action\Action::ROW_ID_PARAMETER
                )
            )
        )->withAsync(),
    ];

    $table = getExampleTable($f)
        ->withActions($actions);


    //render table and results
    $result = [$table];

    $params = [];
    $request = $DIC->http()->request();
    parse_str($request->getUri()->getQuery(), $params);
    if (array_key_exists('demo_table_action', $params)) {
        $query_params_namespace[] = Action::ROW_ID_PARAMETER;
        $param = implode(URLBuilder::SEPARATOR, $query_params_namespace);
        $items = $f->listing()->characteristicValue()->text(
            [
                'table_action' => $params['demo_table_action'],
                'id' => print_r($params[$param] ?? '', true),
            ]
        );

        if ($params['demo_table_action'] === 'do_something_else') {
            echo($r->render($items));
            exit();
        } else {
            $result[] = $f->divider()->horizontal();
            $result[] = $items;
        }
    }

    return $r->render($result);
}

function getExampleTable($f)
{
    $columns = ['f1' => $f->table()->column()->text("Field 1")];

    $data_retrieval = new class () implements I\DataRetrieval {
        public function getRows(
            I\DataRowBuilder $row_builder,
            array $visible_column_ids,
            Range $range,
            Order $order,
            ?array $filter_data,
            ?array $additional_parameters
        ): \Generator {
            foreach (range(0, 5) as $cnt) {
                yield $row_builder->buildDataRow('row_id' . $cnt, ['f1' => $cnt]);
            }
        }

        public function getTotalRowCount(
            ?array $filter_data,
            ?array $additional_parameters
        ): ?int {
            return null;
        }
    };
    return $f->table()->data('a data table with actions', $columns, $data_retrieval);
}
