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

namespace ILIAS\UI\examples\Input\Field\OptionalGroup;

/**
 * ---
 * description: >
 *   Example showing how an optional group (of inputs) which shows, that
 *   the optional input will not be required even though it's sub inputs are.
 *
 * expected output: >
 *   ILIAS shows an optional group titled "this input is not required" and a checkbox with a byline.
 *
 *   Save the checkbox and click "Save":
 *   1. Did you leave the text field empty a red error message is displayed accordingly to the system language.
 *   2. Did you fill out the text field the output looks like the base optional group example.
 *
 *   Deactivate the checkbox and click "Save":
 *   1. No error message is displayed.
 * ---
 */
function with_required_sub_inputs()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    $optional_group = $factory->input()->field()->optionalGroup([
        $factory->input()->field()->text(
            'this input is required',
            'but only if the optional group is checked'
        )->withRequired(true)
    ], 'this input is not required');

    $form = $factory->input()->container()->form()->standard('#', [$optional_group]);

    if ("POST" === $request->getMethod()) {
        $form = $form->withRequest($request);
        $result = $form->getData();
    } else {
        $result = "No result yet.";
    }

    return "<pre>" . print_r($result, true) . "</pre>" . $renderer->render($form);
}
