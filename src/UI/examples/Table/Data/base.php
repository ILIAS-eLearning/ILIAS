<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Table\Data;

use ILIAS\UI\Implementation\Component\Table as T;
use ILIAS\UI\Component\Table as I;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\UI\URLBuilder;
use Psr\Http\Message\ServerRequestInterface;

function base()
{
    global $DIC;
    $f = $DIC['ui.factory'];
    $r = $DIC['ui.renderer'];
    $df = new \ILIAS\Data\Factory();
    $refinery = $DIC['refinery'];
    $request = $DIC->http()->request();
    $current_user_date_format = $df->dateFormat()->withTime24(
        $DIC['ilUser']->getDateFormat()
    );

    /**
     * This is what the table will look like:
     * Columns define the nature (and thus: shape) of one field/aspect of the data record.
     * Also, some functions of the table are set per column, e.g. sortability
     */
    $columns = [
        'usr_id' => $f->table()->column()->number("User ID")
            ->withIsSortable(false),
        'login' => $f->table()->column()->text("Login")
            ->withHighlight(true),
        'email' => $f->table()->column()->eMail("eMail"),
        'last' => $f->table()->column()->date("last login", $current_user_date_format),
        'achieve' => $f->table()->column()->statusIcon("progress")
            ->withIsOptional(true),
        'achieve_txt' => $f->table()->column()->status("success")
            ->withIsSortable(false)
            ->withIsOptional(true),
        'repeat' => $f->table()->column()->boolean("repeat", 'yes', 'no')
            ->withIsSortable(false),
        'fee' => $f->table()->column()->number("Fee")
            ->withDecimals(2)
            ->withUnit('£', I\Column\Number::UNIT_POSITION_FORE)
            ->withOrderingLabels('cheapest first', 'most expensive first'),
        'failure_txt' => $f->table()->column()->status("failure")
            ->withIsSortable(false)
            ->withIsOptional(true, false),
    ];

    /**
     * Define actions:
     * An Action is an URL carrying a parameter that references the targeted record(s).
     * Standard Actions apply to both a collection of records as well as a single entry,
     * while Single- and Multiactions will only work for one of them.
     * Also see the docu-entries for Actions.
    */

    /** this is the endpoint for actions, in this case the same page. */
    $here_uri = $df->uri($DIC->http()->request()->getUri()->__toString());

    /**
     * Actions' commands and the row-ids affected are relayed to the server via GET.
     * The URLBuilder orchestrates query-paramters (a.o. by assigning namespace)
     */
    $url_builder = new URLBuilder($here_uri);
    $query_params_namespace = ['datatable', 'example'];

    /**
     * We have to claim those parameters. In return, there is a token to modify
     * the value of the param; the tokens will work only with the given copy
     * of URLBuilder, so acquireParameters will return the builder as first entry,
     * followed by the tokens.
     */
    list($url_builder, $action_parameter_token, $row_id_token) =
    $url_builder->acquireParameters(
        $query_params_namespace,
        "table_action", //this is the actions's parameter name
        "student_ids"   //this is the parameter name to be used for row-ids
    );

    /**
     * array<string, Action> [action_id => Action]
     */
    $actions = [
        'edit' => $f->table()->action()->single( //never in multi actions
            /** the label as shown in dropdowns */
            'Properties',
            /** set the actions-parameter's value; will become '&datatable_example_table_action=edit' */
            $url_builder->withParameter($action_parameter_token, "edit"),
            /** the Table will need to modify the values of this parameter; give the token. */
            $row_id_token
        ),
        'compare' => $f->table()->action()->multi( //never in single row
            'Add to Comparison',
            $url_builder->withParameter($action_parameter_token, "compare"),
            $row_id_token
        ),
        'delete' =>
            $f->table()->action()->standard( //in both
                'Remove Student',
                $url_builder->withParameter($action_parameter_token, "delete"),
                $row_id_token
            )
            /**
             * An async Action will trigger an AJAX-call to the action's target
             * and display the results in a modal-layer over the Table.
             * Parameters are passed to the call, but you will have to completely
             * build the contents of the response. DO NOT render an entire page ;)
             */
            ->withAsync(),
        'info' =>
            $f->table()->action()->standard( //in both
                'Info',
                $url_builder->withParameter($action_parameter_token, "info"),
                $row_id_token
            )
            ->withAsync()
    ];



    /**
     * Configure the Table to retrieve data with an instance of DataRetrieval;
     * the table itself is agnostic of the source or the way of retrieving records.
     * However, it provides View Controls and therefore parameters that will
     * influence the way data is being retrieved. E.g., it is usually a good idea
     * to delegate sorting to the database, or limit records to the amount of
     * actually shown rows.
     * Those parameters are being provided to DataRetrieval::getRows.
     */
    $data_retrieval = new class ($f, $r) implements I\DataRetrieval {
        public function __construct(
            protected \ILIAS\UI\Factory $ui_factory,
            protected \ILIAS\UI\Renderer $ui_renderer
        ) {
        }

        public function getRows(
            I\DataRowBuilder $row_builder,
            array $visible_column_ids,
            Range $range,
            Order $order,
            ?array $filter_data,
            ?array $additional_parameters
        ): \Generator {
            $records = $this->getRecords($range, $order);
            foreach ($records as $idx => $record) {
                $row_id = (string) $record['usr_id'];
                $record['achieve_txt'] = $record['achieve'] > 80 ? 'passed' : 'failed';
                $record['failure_txt'] = "not " . $record["achieve_txt"];
                $record['repeat'] = $record['achieve'] < 80;

                $icons = [
                    $this->ui_factory->symbol()->icon()->custom('templates/default/images/standard/icon_checked.svg', '', 'small'),
                    $this->ui_factory->symbol()->icon()->custom('templates/default/images/standard/icon_unchecked.svg', '', 'small'),
                    $this->ui_factory->symbol()->icon()->custom('templates/default/images/standard/icon_x.svg', '', 'small'),
                ];
                $icon = $icons[2];
                if($record['achieve'] > 80) {
                    $icon = $icons[0];
                }
                if($record['achieve'] < 30) {
                    $icon = $icons[1];
                }
                $record['achieve'] = $icon;

                yield $row_builder->buildDataRow($row_id, $record)
                    /** Actions may be disabled for specific rows: */
                    ->withDisabledAction('delete', ($record['login'] === 'superuser'));
            }
        }

        public function getTotalRowCount(
            ?array $filter_data,
            ?array $additional_parameters
        ): ?int {
            return count($this->getRecords());
        }

        protected function getRecords(Range $range = null, Order $order = null): array
        {
            $records = [
                ['usr_id' => 123,'login' => 'superuser','email' => 'user@example.com',
                 'last' => (new \DateTimeImmutable())->modify('-1 day') ,'achieve' => 20,'fee' => 0
                ],
                ['usr_id' => 867,'login' => 'student1','email' => 'student1@example.com',
                 'last' => (new \DateTimeImmutable())->modify('-10 day'),'achieve' => 90,'fee' => 40
                ],
                ['usr_id' => 8923,'login' => 'student2','email' => 'student2@example.com',
                 'last' => (new \DateTimeImmutable())->modify('-8 day'),'achieve' => 66,'fee' => 36.789
                ],
                ['usr_id' => 8748,'login' => 'student3_longname','email' => 'student3_long_email@example.com',
                 'last' => (new \DateTimeImmutable())->modify('-300 day'),'achieve' => 8,'fee' => 36.789
                ],
                ['usr_id' => 8749,'login' => 'studentAB','email' => 'studentAB@example.com',
                 'last' => (new \DateTimeImmutable())->modify('-7 day'),'achieve' => 100,'fee' => 114
                ],
                ['usr_id' => 8750,'login' => 'student5','email' => 'student5@example.com',
                 'last' => new \DateTimeImmutable(),'achieve' => 76,'fee' => 3.789
                ],
                ['usr_id' => 8751,'login' => 'student6','email' => 'student6@example.com',
                 'last' => (new \DateTimeImmutable())->modify('-2 day'),'achieve' => 66,'fee' => 67
                ]
            ];
            if ($order) {
                list($order_field, $order_direction) = $order->join([], fn($ret, $key, $value) => [$key, $value]);
                usort($records, fn($a, $b) => $a[$order_field] <=> $b[$order_field]);
                if ($order_direction === 'DESC') {
                    $records = array_reverse($records);
                }
            }
            if ($range) {
                $records = array_slice($records, $range->getStart(), $range->getLength());
            }

            return $records;
        }
    };


    /**
     * setup the Table and hand over the request;
     * with an ID for the table, parameters will be stored throughout url changes
     */
    $table = $f->table()
            ->data('a data table', $columns, $data_retrieval)
            ->withId('example_base')
            ->withActions($actions)
            ->withRequest($request);

    /**
     * build some output.
     */
    $out = [$table];

    /**
     * get the desired action from query; the parameter is namespaced,
     * but we still have the token and it knows its name:
     */
    $query = $DIC->http()->wrapper()->query();
    if ($query->has($action_parameter_token->getName())) {
        $action = $query->retrieve($action_parameter_token->getName(), $refinery->to()->string());
        /** also get the row-ids and build some listing */
        $ids = $query->retrieve($row_id_token->getName(), $refinery->custom()->transformation(fn($v) => $v));
        $listing = $f->listing()->characteristicValue()->text([
            'table_action' => $action,
            'id' => print_r($ids, true),
        ]);

        /** take care of the async-call; 'delete'-action asks for it. */
        if ($action === 'delete') {
            $items = [];
            foreach ($ids as $id) {
                $items[] = $f->modal()->interruptiveItem()->keyValue($id, $row_id_token->getName(), $id);
            }
            echo($r->renderAsync([
                $f->modal()->interruptive(
                    'Deletion',
                    'You are about to delete items!',
                    '#'
                )->withAffectedItems($items)
                ->withAdditionalOnLoadCode(static fn($id): string => "console.log('ASYNC JS');")
            ]));
            exit();
        }
        if ($action === 'info') {
            echo(
                $r->render($f->messageBox()->info('an info message: <br><li>' . implode('<li>', $ids)))
                . '<script data-replace-marker="script">console.log("ASYNC JS, too");</script>'
            );
            exit();
        }

        /** otherwise, we want the table and the results below */
        $out[] = $f->divider()->horizontal();
        $out[] = $listing;
    }

    return $r->render($out);
}
