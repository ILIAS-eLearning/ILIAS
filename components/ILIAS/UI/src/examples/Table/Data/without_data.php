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
use Generator;

/**
 * ---
 * description: >
 *   Example showing a data table without any data and hence no entries, which
 *   will automatically display an according message.
 *
 * expected output: >
 *   ILIAS shows the rendered Component.
 * ---
 */
function without_data(): string
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    $empty_retrieval = new class () implements DataRetrieval {
        public function getRows(
            DataRowBuilder $row_builder,
            array $visible_column_ids,
            Range $range,
            Order $order,
            mixed $additional_viewcontrol_data,
            mixed $filter_data,
            mixed $additional_parameters
        ): Generator {
            yield from [];
        }

        public function getTotalRowCount(
            mixed $additional_viewcontrol_data,
            mixed $filter_data,
            mixed $additional_parameters
        ): ?int {
            return 0;
        }
    };

    $table = $factory->table()->data(
        $empty_retrieval,
        'Empty Data Table',
        [
            'col1' => $factory->table()->column()->text('Column 1')
                ->withIsSortable(false),
            'col2' => $factory->table()->column()->number('Column 2')
                ->withIsSortable(false),
        ],
    );

    //this is only to keep asynch requests from rendering the table.
    $df = new \ILIAS\Data\Factory();
    $refinery = $DIC['refinery'];
    $query = $DIC->http()->wrapper()->query();
    $here_uri = $df->uri($request->getUri()->__toString());
    $url_builder = new \ILIAS\UI\URLBuilder($here_uri);
    $examples_overall_namespace = ['datatable', 'examples', 'async'];
    list($url_builder, $async_token) = $url_builder->acquireParameters(
        $examples_overall_namespace,
        "async"
    );
    if ($query->retrieve(
        $async_token->getName(),
        $refinery->byTrying([$refinery->kindlyTo()->bool(), $refinery->always(false)])
    )
    ) {
        return '';
    };

    return $renderer->render($table->withRequest($request));
}
