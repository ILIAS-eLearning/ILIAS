<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Table\Ordering;

use ILIAS\UI\Implementation\Component\Table as T;
use ILIAS\UI\Component\Table as I;
use ILIAS\Data\URI;

function external()
{
    global $DIC;
    $f = $DIC['ui.factory'];
    $r = $DIC['ui.renderer'];
    $df = new \ILIAS\Data\Factory();
    $refinery = $DIC['refinery'];
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
            I\OrderingRowBuilder $row_builder
        ): \Generator {
            $records = array_values($this->records);
            foreach ($this->records as $position_index => $record) {
                yield $row_builder->buildRow((string)$record['id'], $record);
            }
        }

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
            $r = range(65, 68);
            shuffle($r);

            foreach ($r as $id) {
                $records[(string)$id] = ['id' => $id, 'letter' => chr($id)];
            }
            return $records;
        }
    };

    $out = [];

    /**
     * Alter the URL the tables posts its positions to (here: just add a parameter).
     */
    $target = (new URI((string)$request->getUri()))->withParameter('external', true);
    $table = $f->table()->ordering('sort the letters', $columns, $data_retrieval)
        ->withTargetURL($target);

    /**
     * Set up an endpoint to itercept and customize ordering.
     * Optionally omitting the request is but one option;
     * you may also call call OrderingBinding::withOrder directly,
     * or just modify the data it relies on.
     */
    if ($request_wrapper->has('external') &&
        $request_wrapper->retrieve('external', $refinery->kindlyTo()->bool()) === true
    ) {
        $v = array_keys($request->getParsedBody());
        if($v === range(65, 68)) {
            $out[] = $f->messageBox()->success("ok. great ordering!");
            $table = $table->withRequest($request);
        } else {
            $out[] = $f->messageBox()->failure("nah. try again.");
        }
    }
    $out[] = $table;
    return $r->render($out);
}
