<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Table\Ordering;

use ILIAS\UI\Implementation\Component\Table as T;
use ILIAS\UI\Component\Table as I;
use ILIAS\UI\URLBuilder;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\Data\URI;

/**
 * ---
 * description: >
 *   Example showing an Ordering Table.
 *
 * expected output: >
 *   ILIAS shows the rendered Component.
 *   You may drag a row or give a new value for its position.
 *   Clicking "Save" will return an array with the updated positions
 *   and display the table with the new order applied.
 * ---
 */
function base()
{
    global $DIC;

    /**
     * @var ILIAS\UI\Factory $f;
     */
    $f = $DIC['ui.factory'];

    /**
     * @var ILIAS\UI\Renderer $r;
     */
    $r = $DIC['ui.renderer'];

    /**
     * @var ILIAS\Refinery\Factory $refinery;
     */
    $refinery = $DIC['refinery'];
    $df = new \ILIAS\Data\Factory();
    $request = $DIC->http()->request();
    $request_wrapper = $DIC->http()->wrapper()->query();

    /**
     * Define Columns for the Table; see Data Table for a more extensive exmaple.
     */
    $columns = [
        'word' => $f->table()->column()->text("Word")
            ->withHighlight(true),
        'phrase' => $f->table()->column()->text("Phrase")
            ->withIsOptional(true, false)
    ];

    /**
     * Define Actions for the Table; see Data Table for a more extensive exmaple.
     * Please note that the actions are optional, you may use the OrderingTable
     * without Actions and Checkboxes.
     */
    $url_builder = new URLBuilder($df->uri($request->getUri()->__toString()));
    $query_params_namespace = ['orderingtable', 'example'];
    list($url_builder, $action_parameter_token, $row_id_token) = $url_builder->acquireParameters(
        $query_params_namespace,
        "table_action",
        "ids"
    );
    $actions = [
        $f->table()->action()->standard(
            'Properties',
            $url_builder->withParameter($action_parameter_token, "edit"),
            $row_id_token
        )
    ];


    /**
     * This is the data binding: retrieve rows and write back the order of records.
     */
    $data_retrieval = new class ($f, $r) implements I\OrderingBinding {
        protected array $records;

        public function __construct(
            protected \ILIAS\UI\Factory $ui_factory,
            protected \ILIAS\UI\Renderer $ui_renderer
        ) {
            $this->records = $this->initRecords();
        }

        public function getRows(
            I\OrderingRowBuilder $row_builder,
            array $visible_column_ids
        ): \Generator {
            $records = array_values($this->records);
            foreach ($this->records as $position_index => $record) {
                $row_id = (string) $record['id'];
                yield $row_builder->buildOrderingRow($row_id, $record);
            }
        }

        protected function initRecords(): array
        {
            $r = [
                ['A is for apple', 'it’s red and its green'],
                ['B is for ball', 'and it bounces between'],
                ['C is for cat', 'it’s licking its paws'],
                ['D is for dog', 'it loves playing with balls'],
                ['E is for elephant', 'bigger than me'],
                ['F is for fish', 'he’s missing the sea'],
                ['G for gorilla', 'he’s big and he’s strong'],
                ['H is for home', 'and that’s where I belong'],
                ['I is for insect', 'flying around'],
                ['J  is for jumping', 'jump up and down'],
            ];
            shuffle($r);

            foreach ($r as $record) {
                list($word, $phrase) = $record;
                $id = substr($word, 0, 1);
                $records[$id] = [
                    'id' => $id,
                    'word' => $word,
                    'phrase' => $phrase,
                ];
            }
            return $records;
        }

        /**
         * custom method to store the new order; this is just an example.
         */
        public function setOrder(array $ordered): void
        {
            $r = [];
            foreach ($ordered as $id) {
                $r[(string) $id] = $this->records[(string) $id];
            }
            $this->records = $r;
        }

    };

    $target = (new URI((string) $request->getUri()))->withParameter('ordering_example', 1);
    $table = $f->table()->ordering('ordering table', $columns, $data_retrieval, $target)
        ->withActions($actions)
        ->withRequest($request);

    $out = [];
    if ($request->getMethod() == "POST"
        && $request_wrapper->has('ordering_example')
        && $request_wrapper->retrieve('ordering_example', $refinery->kindlyTo()->int()) === 1
    ) {
        if ($data = $table->getData()) {
            $out[] = $f->legacy('<pre>' . print_r($data, true) . '</pre>');
        }
        $data_retrieval->setOrder($data);
    }

    $out[] = $table;
    return $r->render($out);
}
