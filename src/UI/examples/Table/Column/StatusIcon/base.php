<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Table\Column\StatusIcon;

use ILIAS\UI\Implementation\Component\Table as T;
use ILIAS\UI\Component\Table as I;
use ILIAS\Data\Range;
use ILIAS\Data\Order;

function base()
{
    global $DIC;
    $f = $DIC['ui.factory'];
    $r = $DIC['ui.renderer'];

    $dummy_records = [23, 45, 67];

    $columns = [
        'i1' => $f->table()->column()->statusIcon("icon"),
        'i2' => $f->table()->column()->statusIcon("chart")
    ];

    $data_retrieval = new class ($f, $r, $dummy_records) implements I\DataRetrieval {
        protected \ILIAS\UI\Factory $ui_factory;
        protected \ILIAS\UI\Renderer $ui_renderer;
        protected array $records;

        public function __construct(
            \ILIAS\UI\Factory $ui_factory,
            \ILIAS\UI\Renderer $ui_renderer,
            array $records
        ) {
            $this->ui_factory = $ui_factory;
            $this->ui_renderer = $ui_renderer;
            $this->records = $records;
        }

        public function getRows(
            I\DataRowBuilder $row_builder,
            array $visible_column_ids,
            Range $range,
            Order $order,
            ?array $filter_data,
            ?array $additional_parameters
        ): \Generator {
            foreach ($this->records as $number) {
                $row_id = '';
                $record['i1'] = $this->ui_factory->symbol()->icon()->standard('crs', '', 'small');
                $record['i2'] = $this->ui_factory->symbol()->icon()->custom(
                    'templates/default/images/standard/icon_checked.svg',
                    '',
                    'small'
                );
                yield $row_builder->buildDataRow($row_id, $record);
            }
        }

        public function getTotalRowCount(
            ?array $filter_data,
            ?array $additional_parameters
        ): ?int {
            return count($this->records);
        }
    };

    $table = $f->table()->data('StatusIcons Columns', $columns, $data_retrieval)
        ->withRequest($DIC->http()->request());
    return $r->render($table);
}
