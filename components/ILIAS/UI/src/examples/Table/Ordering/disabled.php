<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Table\Ordering;

use ILIAS\UI\Implementation\Component\Table as T;
use ILIAS\UI\Component\Table as I;
use ILIAS\UI\URLBuilder;
use Psr\Http\Message\ServerRequestInterface;

function disabled()
{
    global $DIC;
    $f = $DIC['ui.factory'];
    $r = $DIC['ui.renderer'];
    $df = new \ILIAS\Data\Factory();
    $refinery = $DIC['refinery'];
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
            I\OrderingRowBuilder $row_builder
        ): \Generator {
            foreach (array_values($this->records) as $record) {
                yield $row_builder->buildRow((string)$record['id'], $record);
            }
        }

        public function withOrder(array $ordered): self
        {
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
    $table = $f->table()->ordering('ordering table with disabled ordering', $columns, $data_retrieval)
        ->withOrderingDisabled(true);

    return $r->render($table);
}
