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

use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\UI\URLBuilder;

/**
 * ---
 * description: >
 *   Data Table may highlight rows.
 *
 * expected output: >
 *   Example showing a data table with a highlighted row.
 *   Below the table is a button "Highlight some rows".
 *   When clicked, the page reloads and two more columns are highlighted.
 * ---
 */
function highlight_rows(): string
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $df = new \ILIAS\Data\Factory();
    $refinery = $DIC['refinery'];

    $request = $DIC->http()->request();
    $query = $DIC->http()->wrapper()->query();

    $here_uri = $df->uri($request->getUri()->__toString());
    $url_builder = new URLBuilder($here_uri);

    $namespace = ['dt', 'highlight'];
    list($url_builder, $highlight_token) = $url_builder->acquireParameters(
        $namespace,
        'highlighted'
    );

    $records = [
        ['col1' => 1, 'col2' => 'a'],
        ['col1' => 2, 'col2' => 'b'],
        ['col1' => 3, 'col2' => 'c'],
        ['col1' => 4, 'col2' => 'd'],
        ['col1' => 5, 'col2' => 'e'],
    ];

    $data_retrieval = new class ($records) implements DataRetrieval {
        public function __construct(
            protected array $records
        ) {
        }

        public function getRows(
            DataRowBuilder $row_builder,
            array $visible_column_ids,
            Range $range,
            Order $order,
            mixed $additional_viewcontrol_data,
            mixed $filter_data,
            mixed $additional_parameters
        ): \Generator {
            foreach ($this->records as $record) {
                $row_id = (string) $record['col1'];
                yield $row_builder->buildDataRow($row_id, $record)
                    ->withHighlighted($row_id === '4');
            }
        }

        public function getTotalRowCount(
            mixed $additional_viewcontrol_data,
            mixed $filter_data,
            mixed $additional_parameters
        ): ?int {
            return count($this->records);
        }
    };

    $table = $factory->table()->data(
        $data_retrieval,
        'Data Table with Highlighted Rows',
        [
            'col1' => $factory->table()->column()->number('Column 1')
                ->withIsSortable(false),
            'col2' => $factory->table()->column()->text('Column 2')
                ->withIsSortable(false),
        ],
    )
    ->withHighlightToken($highlight_token)
    ->withRequest($request);

    $button = $factory->button()->standard(
        'Highlight some rows',
        $url_builder
            ->withParameter($highlight_token, ['1', '2'])
            ->buildURI()
            ->__toString()
    );
    return $renderer->render([
        $table,
        $button
    ]);
}
