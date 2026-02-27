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

namespace ILIAS\UI\examples\Input\Field\Radio;

use ILIAS\Filesystem\Stream\Streams;
use ilSystemStyleDocumentationGUI;

/**
 * ---
 * description: >
 *   An example using the Radio Input with an Option Filter using async option loading.
 *
 * expected output: >
 *   A Radio with Search allowing to select an option from an async data source.
 *   When expanded, there is a list of options and a search input field.
 *   When entering letters into the search input, an async data source delivers matching options.
 *   An option can be selected and will be pinned to the top of the list.
 *   A clear filter button resets the search input field.
 *   When collapsed, the selected options are still being shown as a read-only preview.
 *   On screen readers, the number of filtered results is announced.
 * ---
 */
function with_has_option_filter_data_source(): string
{
    //Step 1: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $ctrl = $DIC->ctrl();
    $renderer = $DIC->ui()->renderer();
    $http = $DIC->http();
    $http_wrapper = $http->wrapper();
    $refinery = $DIC->refinery();
    $DIC->ui()->mainTemplate()->addCss('./assets/ui-examples/css/radio_filterable_section_style.css');

    //Step 2: Define async response endpoint
    if ($http_wrapper->query()->has('display_values') || $http_wrapper->query()->has('term')) {
        $data = [
            'admin' => 'Administrator',
            'user' => 'User',
            'custom1' => 'My Custom Role 1',
            'custom2' => 'My Custom Role 2',
            'custom3' => 'My Custom Role 3'
        ];

        $display_values = $http_wrapper->query()->retrieve(
            "display_values",
            $refinery->byTrying([
                $refinery->kindlyTo()->listOf($refinery->kindlyTo()->string()),
                $refinery->always(null)
            ])
        );

        if ($display_values) {
            $result = [];

            foreach ($display_values as $display_value) {
                if ($data[$display_value]) {
                    $result[] = [
                        'value' => urlencode($refinery->encode()->htmlSpecialCharsAsEntities()->transform($display_value)),
                        'display' => $data[$display_value],
                        'searchBy' => $display_value
                    ];
                }
            }

            $http->saveResponse(
                $http->response()->withBody(
                    Streams::ofString(json_encode($result, JSON_THROW_ON_ERROR))
                )
            );
            $http->sendResponse();
            $http->close();
        }

        $search_term = $http_wrapper->query()->retrieve(
            "term",
            $refinery->byTrying([
                $refinery->kindlyTo()->string(),
                $refinery->always('')
            ])
        );

        $result = [];
        foreach ($data as $value => $display_value) {
            if (str_contains($display_value, $search_term)) {
                $result[] = [
                    'value' => urlencode($refinery->encode()->htmlSpecialCharsAsEntities()->transform($value)),
                    'display' => $display_value,
                    'searchBy' => $value
                ];
            }
        }

        $http->saveResponse(
            $http->response()->withBody(
                Streams::ofString(json_encode($result, JSON_THROW_ON_ERROR))
            )
        );
        $http->sendResponse();
        $http->close();
    }

    //Step 3: define the radio
    $ctrl->setParameterByClass(ilSystemStyleDocumentationGUI::class, 'node_id', 'InputFieldRadioRadio');
    $async_radio = $ui->input()->field()->radio(
        "User",
        "Select a single user login provided by a data source"
    )
        ->withHasOptionFilter(true, $ctrl->getLinkTargetByClass(ilSystemStyleDocumentationGUI::class, 'entries', '', true));
    $async_radio = $async_radio->withValue("admin");

    //Step 4: define form and form actions
    $form = $ui->input()->container()->form()->standard('#', ['radio' => $async_radio]);

    //Step 5: Render the radio with the enclosing form.
    return $renderer->render($form);
}
