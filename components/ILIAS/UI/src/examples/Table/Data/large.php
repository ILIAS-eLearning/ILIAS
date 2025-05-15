<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Table\Data;

use ILIAS\UI\Implementation\Component\Table as T;
use ILIAS\UI\Component\Table as I;
use ILIAS\UI\URLBuilder;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\Data\URI;
use ILIAS\Data\Range;
use ILIAS\Data\Order;

/**
 * ---
 * description: >
 *   Example showing an Data Table with large (id-)entries.
 *
 * expected output: >
 *   ILIAS shows the rendered Component.
 *   Select all rows using the "+"-icon on the top left.
 *   A dialog should warn you that the expected URL is too large.
 *   Deselect / select some rows with the checkbox.
 *   It should not be possible to select more than 7 rows simultaniously.
 * ---
 */
function large()
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

    $columns = [
        'entry' => $f->table()->column()->text("some entry")
    ];

    $url_builder = new URLBuilder($df->uri($request->getUri()->__toString()));
    $query_params_namespace = ['datatable', 'example', 'large'];
    list($url_builder, $action_parameter_token, $row_id_token) = $url_builder->acquireParameters(
        $query_params_namespace,
        "table_action",
        "a_quiet_longish_parameter_name_to_quickly_exceed_url_limits"
    );

    $actions = [
        $f->table()->action()->standard(
            'some action',
            $url_builder->withParameter($action_parameter_token, "edit"),
            $row_id_token
        )
    ];

    $data_retrieval = new class ($f, $r) implements I\DataRetrieval {
        protected array $records;

        public function __construct(
            protected \ILIAS\UI\Factory $ui_factory,
            protected \ILIAS\UI\Renderer $ui_renderer
        ) {
            $this->records = $this->initRecords();
        }

        public function getRows(
            I\DataRowBuilder $row_builder,
            array $visible_column_ids,
            Range $range,
            Order $order,
            ?array $filter_data,
            ?array $additional_parameters
        ): \Generator {
            $records = array_values($this->records);
            foreach ($this->records as $record) {
                $row_id = (string) $record['id'];
                yield $row_builder->buildDataRow($row_id, $record);
            }
        }

        public function getTotalRowCount(
            ?array $filter_data,
            ?array $additional_parameters
        ): ?int {
            return count($this->records);
        }

        protected function initRecords(): array
        {
            $records = [];
            foreach (array_map('strval', range(0, 9)) as $r) {
                $id = str_repeat($r, 1000);
                $records[$id] = [
                    'id' => $id,
                    'entry' => substr($id, 0, 50),
                ];
            }
            return $records;
        }
    };

    $target = (new URI((string) $request->getUri()))->withParameter('ordering_example', 4);
    $table = $f->table()->data('large ids data table', $columns, $data_retrieval)
        ->withActions($actions)
        ->withRequest($request);

    return $r->render($table);
}
