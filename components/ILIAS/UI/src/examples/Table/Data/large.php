<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
     * @var \ILIAS\UI\Factory $f;
     */
    $f = $DIC['ui.factory'];

    /**
     * @var \ILIAS\UI\Renderer $r;
     */
    $r = $DIC['ui.renderer'];

    /**
     * @var \ILIAS\Refinery\Factory $refinery;
     */
    $refinery = $DIC['refinery'];
    $df = new \ILIAS\Data\Factory();
    $request = $DIC->http()->request();
    $request_wrapper = $DIC->http()->wrapper()->query();

    $columns = [
        'entry' => $f->table()->column()->text("some entry")
    ];

    $url_builder = new URLBuilder($df->uri($request->getUri()->__toString()));
    $examples_overall_namespace = ['datatable', 'examples', 'async'];
    list($url_builder, $async_token) = $url_builder->acquireParameters(
        $examples_overall_namespace,
        "async"
    );

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
            mixed $additional_viewcontrol_data,
            mixed $filter_data,
            mixed $additional_parameters
        ): \Generator {
            $records = array_values($this->records);
            foreach ($this->records as $record) {
                $row_id = (string) $record['id'];
                yield $row_builder->buildDataRow($row_id, $record);
            }
        }

        public function getTotalRowCount(
            mixed $additional_viewcontrol_data,
            mixed $filter_data,
            mixed $additional_parameters
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
    $table = $f->table()->data($data_retrieval, 'large ids data table', $columns)
        ->withActions($actions)
        ->withRequest($request);

    $query = $DIC->http()->wrapper()->query();
    if ($query->retrieve(
        $async_token->getName(),
        $refinery->byTrying([$refinery->kindlyTo()->bool(), $refinery->always(false)])
    )
    ) {
        return '';
    };

    return $r->render($table);
}
