<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Table\Ordering;

use ILIAS\UI\Implementation\Component\Table as T;
use ILIAS\UI\Component\Table as I;
use ILIAS\Data\URI;

function external()
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
            $records = array_values($this->records);
            foreach ($this->records as $position_index => $record) {
                yield $row_builder->buildOrderingRow((string)$record['id'], $record);
            }
        }

        protected function initRecords(): array
        {
            $r = range(65, 68);
            shuffle($r);

            foreach ($r as $id) {
                $records[(string)$id] = ['id' => $id, 'letter' => chr($id)];
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

    $out = [];

    /**
     * Alter the URL the tables posts its positions to (here: just add a parameter).
     */
    $target = (new URI((string)$request->getUri()))->withParameter('external', true);
    $table = $f->table()->ordering('sort the letters', $columns, $data_retrieval)
        ->withTargetURL($target)
        ->withRequest($request);

    /**
     * Set up an endpoint to itercept and customize ordering,
     * here: store only correct order
     */
    if ($request_wrapper->has('external') &&
        $request_wrapper->retrieve('external', $refinery->kindlyTo()->bool()) === true
    ) {
        $data = $table->withRequest($request)->getData();
        if($data === range(65, 68)) {
            $data_retrieval->setOrder($data);
            $out[] = $f->messageBox()->success("ok. great ordering!");
        } else {
            $out[] = $f->messageBox()->failure("nah. try again.");
        }
    }
    $out[] = $table;
    return $r->render($out);
}
