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
 *   Example showing a data table with a button to create a new entry.
 *
 * expected output: >
 *   ILIAS shows the rendered Component.
 * ---
 */
function creation(): string
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
    $examples_overall_namespace = ['datatable', 'examples', 'async'];
    list($url_builder, $async_token) = $url_builder->acquireParameters(
        $examples_overall_namespace,
        "async"
    );
    $namespace = ['dt', 'creation'];
    list($url_builder, $action_token) = $url_builder->acquireParameters(
        $namespace,
        "action"
    );

    $prompt_uri = $url_builder
            ->withParameter($async_token, 'true')
            ->withParameter($action_token, "create");

    $prompt = $factory->prompt()->standard($prompt_uri);

    $action = $query->retrieve(
        $action_token->getName(),
        $refinery->byTrying([$refinery->kindlyTo()->string(), $refinery->always(null)])
    );

    if ($action === 'create') {
        $form = $factory->input()->container()->form()->standard(
            $prompt_uri->buildURI()->__toString(),
            [
                $factory->input()->field()->numeric('Column 1')->withRequired(true),
                $factory->input()->field()->text('Column 2')->withRequired(true)
            ]
        );

        if ($request->getMethod() === 'POST') {
            $form = $form->withRequest($request);
            if ($data = $form->getData()) {

                //create record based on data...
                $recs = \ilSession::get('dt_example_rec') ?? [];
                $recs[] = ['col1' => $data[0], 'col2' => $data[1]];
                \ilSession::set('dt_example_rec', $recs);

                $response = $factory->prompt()->state()->redirect(
                    $url_builder
                        ->withParameter($async_token, 'false')
                        ->withParameter($action_token, '')
                        ->buildURI()
                );
                echo($renderer->renderAsync($response));
                exit();
            }
        }

        $response = $factory->prompt()->state()->show($form);
        echo($renderer->renderAsync($response));
        exit();
    }



    $records = [
        ['col1' => 1, 'col2' => 'a'],
        ['col1' => 2, 'col2' => 'b'],
    ] + (\ilSession::get('dt_example_rec') ?? []);



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
            foreach ($this->records as $idx => $record) {
                yield $row_builder->buildDataRow('_' . $idx, $record);
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
        'Data Table with create button',
        [
            'col1' => $factory->table()->column()->number('Column 1')
                ->withIsSortable(false),
            'col2' => $factory->table()->column()->text('Column 2')
                ->withIsSortable(false),
        ],
    );

    if ($query->retrieve(
        $async_token->getName(),
        $refinery->byTrying([$refinery->kindlyTo()->bool(), $refinery->always(false)])
    )
    ) {
        return '';
    };

    return $renderer->render(
        $table
        ->withEntryCreation($prompt)
        ->withRequest($request)
    );
}
