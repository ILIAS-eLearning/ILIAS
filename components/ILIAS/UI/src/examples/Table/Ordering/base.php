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
    $f = $DIC['ui.factory'];
    $r = $DIC['ui.renderer'];
    $df = new \ILIAS\Data\Factory();
    $refinery = $DIC['refinery'];
    $request = $DIC->http()->request();

    /**
     * Define Columns for the Table; see Data Table for a more extensive exmaple.
     */
    $columns = [
        'id' => $f->table()->column()->number("ID"),
        'letter' => $f->table()->column()->text("Letter")
            ->withHighlight(true)
    ];

    /**
     * Define Actions for the Table; see Data Table for a more extensive exmaple.
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
            foreach ($this->records as $idx => $record) {
                $row_id = (string)$record['id'];
                yield $row_builder->buildRow($row_id, $record);
            }
        }

        /**
         * $ordered gives an array with the row ids in the new order.
         */
        public function withOrder(array $ordered): self
        {
            $r = [];
            foreach ($ordered as $id) {
                $r[(string)$id] = $this->records[(string)$id];
            }
            $clone = clone $this;
            $clone->records = $r;
            return $clone;
        }

        protected function initRecords(): array
        {
            $r = range(65, 75);
            shuffle($r);

            foreach ($r as $id) {
                $records[(string)$id] = [
                    'id' => $id,
                    'letter' => chr($id)
                ];
            }
            return $records;
        }
    };


    $table = $f->table()->ordering('ordering table', $columns, $data_retrieval)
        ->withActions($actions);

    if ($request->getMethod() == "POST") {
        $table = $table->withRequest($request);
    }

    return $r->render($table);
}
