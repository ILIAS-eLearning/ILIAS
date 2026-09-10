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
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\UI\URLBuilder;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use Psr\Http\Message\ServerRequestInterface;

/**
 * ---
 * expected output: >
 *   ILIAS shows the rendered Component.
 * ---
 */
function summary_rows()
{
    global $DIC;
    $f = $DIC['ui.factory'];
    $r = $DIC['ui.renderer'];
    $df = new DataFactory();
    $request = $DIC->http()->request();

    $columns = [
        'id' => $f->table()->column()->number("id"),
        'transaction' => $f->table()->column()->boolean(
            "Transaction",
            $f->symbol()->glyph()->down(),
            $f->symbol()->glyph()->up()->withHighlight(),
        ),
        'value' => $f->table()->column()->number("Value")
            ->withUnit('€', I\Column\Number::UNIT_POSITION_FORE)
            ->withDecimals(2)
            ->withHighlight(true)
    ];

    $here_uri = $df->uri($DIC->http()->request()->getUri()->__toString());
    $url_builder = new URLBuilder($here_uri);
    $ns = ['datatable', 'summary'];
    list($url_builder, $action_parameter_token, $row_id_token) =
    $url_builder->acquireParameters($ns, "ta", "ids");
    $actions = [
        'some action' => $f->table()->action()->standard(
            'act',
            $url_builder->withParameter($action_parameter_token, "act"),
            $row_id_token
        )
    ];

    /**
     * implement DataRetrievalWithHeaderSummary to add a summary-row to the header
     */
    $data_retrieval = new class () implements I\DataRetrievalWithHeaderSummary {
        public function getHeaderSummary(
            DataFactory $data_factory,
            array $visible_column_ids,
            mixed $filter_data,
            mixed $additional_parameters
        ): array {
            $sum_total = array_reduce(
                $this->getRecords(),
                fn($total, $r) => $total += $r['value']
            );
            $sum_total = $data_factory->text()->markdown()->wordOnly(
                'balance (all records): **€ ' . $sum_total . '**'
            );
            return ['value' => $sum_total];
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
            $records = $this->getRecords($range, $order);
            $counter = 0;
            $sum = 0;
            foreach ($records as $idx => $record) {
                $row_id = (string) $record['id'];
                $record['transaction'] = $record['value'] >= 0;
                yield $row_builder->buildDataRow($row_id, $record);

                $sum += $record['value'];
                $counter += 1;
                $subtotal = 'subtotal: **€' . $sum . '**';
                if ($counter == 5) {
                    yield $row_builder->buildSummaryRow(['value' => $subtotal]);
                    $sum = 0;
                    $counter = 0;
                }
            }
            if ($counter > 0) {
                yield $row_builder->buildSummaryRow(['value' => $subtotal]);
            }

            $total = 'total: **€'
                . array_reduce($records, fn($total, $r) => $total += $r['value'])
                . '**';
            yield $row_builder->buildSummaryRow(['value' => $total]);
        }

        public function getTotalRowCount(
            mixed $additional_viewcontrol_data,
            mixed $filter_data,
            mixed $additional_parameters
        ): ?int {
            return count($this->getRecords());
        }

        protected function getRecords(?Range $range = null, ?Order $order = null): array
        {
            $records = [
                ['id' => 482, 'value' => 712.23],
                ['id' => 915, 'value' => 654.89],
                ['id' => 237, 'value' => -128.45],
                ['id' => 768, 'value' => 945.12],
                ['id' => 391, 'value' => 523.77],
                ['id' => 654, 'value' => -837.50],
                ['id' => 102, 'value' => 275.64],
                ['id' => 846, 'value' => 999.99],
                ['id' => 529, 'value' => -412.08],
                ['id' => 783, 'value' => -601.34],
                ['id' => 315, 'value' => -350.70],
                ['id' => 209, 'value' => -780.15]
            ];

            if ($order) {
                list($order_field, $order_direction) = $order->join([], fn($ret, $key, $value) => [$key, $value]);
                if (array_key_exists($order_field, current($records))) {
                    usort($records, fn($a, $b) => $a[$order_field] <=> $b[$order_field]);
                    if ($order_direction === 'DESC') {
                        $records = array_reverse($records);
                    }
                }
            }
            if ($range) {
                $records = array_slice($records, $range->getStart(), $range->getLength());
            }
            return $records;
        }
    };

    $table = $f->table()
            ->data($data_retrieval, 'summaries', $columns)
            ->withId('example_summary')
            ->withActions($actions)
            ->withRequest($request);

    return $r->render($table);
}
