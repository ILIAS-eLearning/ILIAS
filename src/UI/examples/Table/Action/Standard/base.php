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
    $refinery = $DIC['refinery'];

    //this is the endpoint for actions, in this case the same page.
    $here_uri = $df->uri($DIC->http()->request()->getUri()->__toString());
    $url_builder = new URLBuilder($here_uri);

    //these are the query parameters this instance is controlling
    $query_params_namespace = ['datatable', 'example'];
    list($url_builder, $id_token, $action_token) = $url_builder->acquireParameters(
        $query_params_namespace,
        "some_row_id",
        "table_action"
    );

    //define standard (both single and multi) actions for the table
    $actions = [
        $f->table()->action()->standard(
            'do this',
            $url_builder->withParameter($action_token, "do_something"),
            $id_token
        ),
        $f->table()->action()->standard(
            'do something else',
            $url_builder->withParameter($action_token, "do_something_else"),
            $id_token
        )->withAsync(),
    ];

    $table = getExampleTable($f)
        ->withActions($actions)
        ->withRequest($DIC->http()->request());


    //render table and results
    $result = [$table];

    $query = $DIC->http()->wrapper()->query();
    if ($query->has($action_token->getName())) {
        $action = $query->retrieve($action_token->getName(), $refinery->to()->string());
        $ids = $query->retrieve($id_token->getName(), $refinery->custom()->transformation(fn ($v) => $v));

        if ($action === 'do_something_else') {
            $items = [];
            $ids = explode(',', $ids);
            foreach ($ids as $id) {
                $items[] = $f->modal()->interruptiveItem()->keyValue($id, $id_token->getName(), $id);
            }
            echo($r->renderAsync([
                $f->modal()->interruptive(
                    'do something else',
                    'affected items',
                    '#'
                )->withAffectedItems($items)
            ]));
            exit();
        } else {
            $items = $f->listing()->characteristicValue()->text(
                [
                    'table_action' => $action,
                    'id' => print_r($ids, true),
                ]
            );
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
            return 6;
        }
    };
    return $f->table()->data('a data table with actions', $columns, $data_retrieval);
}
