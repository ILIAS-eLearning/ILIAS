<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Table\Ordering;

use ILIAS\UI\Implementation\Component\Table as T;
use ILIAS\UI\Component\Table as I;
use ILIAS\UI\URLBuilder;
use Psr\Http\Message\ServerRequestInterface;

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
            ->withHighlight(true)
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
            I\OrderingRowBuilder $row_builder
        ): \Generator {
            $records = array_values($this->records);
            foreach ($this->records as $position_index => $record) {
                $row_id = (string)$record['id'];
                yield $row_builder->buildOrderingRow($row_id, $record);
            }
        }

        protected function initRecords(): array
        {
            $r = [
                'A is for apple',
                'B is for ball',
                'C is for cat',
                'D is for dog',
                'E is for elephant',
                'F is for fish',
                'G for gorilla',
                'H is for home',
                'I is for insect',
                'J  is for jumping',
            ];
            shuffle($r);

            foreach ($r as $index => $word) {
                $id = substr($word, 0, 1);
                $records[$id] = [
                    'id' => $id,
                    'word' => $r[$index]
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
                $r[(string)$id] = $this->records[(string)$id];
            }
            $this->records = $r;
        }

    };

    $table = $f->table()->ordering('ordering table', $columns, $data_retrieval)
        ->withActions($actions);

    $out = [];
    if ($request->getMethod() == "POST"
        && !$request_wrapper->has('external') // do not listen to 3rd example
    ) {
        $table = $table->withRequest($request);
        if($data = $table->getData()) {
            $out[] = $f->legacy('<pre>' . print_r($data, true) . '</pre>');
        }
        $data_retrieval->setOrder($data);
    }

    $out[] = $table;
    return $r->render($out);
}
