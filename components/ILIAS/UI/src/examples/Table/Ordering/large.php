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

namespace ILIAS\UI\examples\Table\Ordering;

use ILIAS\UI\Component\Table as I;
use ILIAS\UI\URLBuilder;
use ILIAS\Data\URI;

/**
 * ---
 * description: >
 *   Example showing an Ordering Table with large (id-)entries.
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

    /**
     * Define Columns for the Table; see Data Table for a more extensive exmaple.
     */
    $columns = [
        'entry' => $f->table()->column()->text("some entry")
    ];

    /**
     * Define Actions for the Table; see Data Table for a more extensive exmaple.
     * Please note that the actions are optional, you may use the OrderingTable
     * without Actions and Checkboxes.
     */
    $url_builder = new URLBuilder($df->uri($request->getUri()->__toString()));
    $query_params_namespace = ['orderingtable', 'example', 'large'];
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


    /**
     * This is the data binding: retrieve rows and write back the order of records.
     */
    $data_retrieval = new class ($f, $r) implements I\OrderingRetrieval {
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

    $target = (new URI((string) $request->getUri()))->withParameter('ordering_example', 4);
    $table = $f->table()->ordering($data_retrieval, $target, 'large ids ordering table', $columns)
        ->withActions($actions)
        ->withRequest($request);

    $out = [];
    if ($request->getMethod() == "POST"
        && $request_wrapper->has('ordering_example')
        && $request_wrapper->retrieve('ordering_example', $refinery->kindlyTo()->int()) === 4
    ) {
        if ($data = $table->getData()) {
            $out[] = $f->legacy()->content('<pre>' . print_r($data, true) . '</pre>');
        }
        $data_retrieval->setOrder($data);
    }

    $out[] = $table;
    return $r->render($out);
}
