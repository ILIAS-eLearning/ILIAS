<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Table\Data;

use ILIAS\UI\Implementation\Component\Table as T;
use ILIAS\UI\Component\Table as I;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\Data\URI;
use Psr\Http\Message\ServerRequestInterface;

function with_actions()
{
    global $DIC;
    $f = $DIC['ui.factory'];
    $r = $DIC['ui.renderer'];
    $ctrl = $DIC['ilCtrl'];
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
     * An Action is a Signal or URL carrying a parameter that references
     * the targeted record(s).
     * Standard Actions apply to both a collection of records as well as
     * a single entry, while Single- and Multiactions will only work for
     * one of them.
    */
    $modal = getSomeExampleModal($f, $ctrl);
    $signal = $modal->getShowSignal();

    $actions = [
        //never in multi actions
        'edit' => $f->table()->action()->single('edit', 'ids', buildDemoURL($request, 'table_action=edit')),
        //never in single row
        'compare' => $f->table()->action()->multi('compare', 'ids', buildDemoURL($request, 'table_action=compare')),
        //in both
        'delete' => $f->table()->action()->standard('delete', 'ids', $signal)
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

function buildDemoURL(ServerRequestInterface $request, string $param): URI
{
    $df = new \ILIAS\Data\Factory();
    return $df->uri($request->getUri()->__toString() . '&' . $param);
}

function getSomeExampleModal($factory, $ctrl)
{
    $form_action = $ctrl->getFormActionByClass('ilsystemstyledocumentationgui');
    $modal = $factory->modal()->interruptive('Delete', 'really delete?', $form_action);
    return $modal;
}
