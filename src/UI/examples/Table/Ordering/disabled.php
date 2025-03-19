<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Table\Ordering;

use ILIAS\UI\Implementation\Component\Table as T;
use ILIAS\UI\Component\Table as I;
use ILIAS\Data\URI;

/**
 * ---
 * description: >
 *   Example showing an Ordering Table.
 *
 * expected output: >
 *   ILIAS shows the rendered Component.
 *   You may NOT drag any row; there are no Number Inputs for row-positions.
 * ---
 */
function disabled()
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

    $request = $DIC->http()->request();

    $columns = [
        'id' => $f->table()->column()->number("ID"),
        'letter' => $f->table()->column()->text("Letter")
            ->withHighlight(true)
    ];

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
            foreach (array_values($this->records) as $record) {
                yield $row_builder->buildOrderingRow((string) $record['id'], $record);
            }
        }

        protected function initRecords(): array
        {
            $r = range(65, 68);
            shuffle($r);
            return array_map(fn($id) => ['id' => $id,'letter' => chr($id)], $r);
        }
    };

    /**
     * Disable the ordering (e.g. due to missing permissions)
     */
    $target = (new URI((string) $request->getUri()));
    $table = $f->table()->ordering('ordering table with disabled ordering', $columns, $data_retrieval, $target)
        ->withOrderingDisabled(true)
        ->withRequest($request);

    return $r->render($table);
}
