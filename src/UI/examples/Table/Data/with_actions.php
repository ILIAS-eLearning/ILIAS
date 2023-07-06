<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Table\Data;

use ILIAS\UI\Implementation\Component\Table as T;
use ILIAS\UI\Component\Table as I;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\UI\URLBuilder;
use Psr\Http\Message\ServerRequestInterface;

function with_actions()
{
    global $DIC;
    $f = $DIC['ui.factory'];
    $r = $DIC['ui.renderer'];
    $df = new \ILIAS\Data\Factory();
    $refinery = $DIC['refinery'];
    $request = $DIC->http()->request();

    // This is what the table will look like
    $columns = [
        'f1' => $f->table()->column()->text("Field 1"),
        'f2' => $f->table()->column()->text("Field 2"),
        'f0' => $f->table()->column()->status("empty"),
        'f3' => $f->table()->column()->number("Field 3")->withDecimals(2),
        'f4' => $f->table()->column()->number("Field 4")
    ];

    /**
     * Define actions:
     * An Action is a URL carrying a parameter that references the targeted record(s).
     * Standard Actions apply to both a collection of records as well as
     * a single entry, while Single- and Multiactions will only work for
     * one of them.
    */

    //this is the endpoint for actions, in this case the same page.
    $here_uri = $df->uri($DIC->http()->request()->getUri()->__toString());
    $url_builder = new URLBuilder($here_uri);

    //these are the query parameters this instance is controlling
    $query_params_namespace = ['datatable', 'example'];
    list($url_builder, $id_token, $action_token) = $url_builder->acquireParameters(
        $query_params_namespace,
        "ids", //this is the parameter name to be used for rowids
        "table_action" //this is the actions's parameter name
    );

    $actions = [
        //never in multi actions
        'edit' => $f->table()->action()->single('edit', $url_builder->withParameter($action_token, "edit"), $id_token),
        //never in single row
        'compare' => $f->table()->action()->multi('compare', $url_builder->withParameter($action_token, "compare"), $id_token),
        //in both
        'delete' => $f->table()->action()->standard('delete', $url_builder->withParameter($action_token, "delete"), $id_token)
            ->withAsync(),
    ];

    // retrieve data and map records to table rows
    $data_retrieval = new class () implements I\DataRetrieval {
        public function getRows(
            I\DataRowBuilder $row_builder,
            array $visible_column_ids,
            Range $range,
            Order $order,
            ?array $filter_data,
            ?array $additional_parameters
        ): \Generator {
            foreach ($this->getRecords() as $idx => $record) {
                //identify record (e.g. for actions)
                $row_id = 'rowid-' . (string) $idx;

                //maybe do something with the record
                $record['f4'] = $record['f3'] * 2;

                //decide on availability of actions
                $not_to_be_edited = $record['f3'] > 4;
                $not_to_be_deleted = $record['f3'] > 4 && $record['f3'] < 5;

                //and yield the row
                yield $row_builder->buildDataRow($row_id, $record)
                    ->withDisabledAction('edit', $not_to_be_edited)
                    ->withDisabledAction('delete', $not_to_be_deleted);
            }
        }

        public function getTotalRowCount(
            ?array $filter_data,
            ?array $additional_parameters
        ): ?int {
            return count($this->getRecords());
        }

        //this is some dummy-data:
        protected function getRecords(): array
        {
            return  [
                ['f1' => 'value1.1','f2' => 'value1.2','f3' => 1.11],
                ['f1' => 'value2.1','f2' => 'value2.2','f3' => 2.22],
                ['f1' => 'value3.1','f2' => 'value3.2','f3' => 4.44],
                ['f1' => 'value4.1','f2' => 'value4.2','f3' => 8.88]
            ];
        }
    };

    //setup the table
    $table = $f->table()->data('a data table with actions', $columns, $data_retrieval)
        ->withActions($actions);

    //apply request and render
    $out = [
        $table->withRequest($request)
    ];

    //demo results
    $query = $DIC->http()->wrapper()->query();
    if ($query->has($action_token->getName())) {
        $action = $query->retrieve($action_token->getName(), $refinery->to()->string());
        $ids = $query->retrieve($id_token->getName(), $refinery->custom()->transformation(fn($v) => $v));
        $listing = $f->listing()->characteristicValue()->text(
            [
                'table_action' => $action,
                'id' => print_r($ids, true),
            ]
        );

        if ($action === 'delete') {
            echo($r->render([
                $f->messageBox()->confirmation('You are about to delete items!'),
                $f->divider()->horizontal(),
                $listing
            ]));
            exit();
        }

        $out[] = $f->divider()->horizontal();
        $out[] = $listing;
    }

    return $r->render($out);
}
