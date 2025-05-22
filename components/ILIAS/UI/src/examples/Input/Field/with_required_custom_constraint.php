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

namespace ILIAS\UI\examples\Input\Field;

/**
 */
/**
 * ---
 * description: >
 *   Example showing the use of the withRequired() method
 *   with a custom constraint that replaces the default requirement constraint.
 *   A custom constraint SHOULD be explained in the byline of the input.
 *
 * expected output: >
 *   ILIAS shows the rendered Component.
 * ---
 */
function with_required_custom_constraint()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $refinery = $DIC->refinery();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    // Step 1: define the text field, make it a required field
    // and add a custom constraint
    $text_input = $ui->input()->field()->text("Enter a name", "Needs to start with an H");
    $custom_constraint = $refinery->custom()->constraint(function ($value) {
        return (substr($value, 0, 1) === 'H') ? true : false;
    }, "Name does not start with an H");
    $text_input = $text_input->withRequired(true, $custom_constraint);

    //Step 2: define form and form actions
    $form = $ui->input()->container()->form()->standard('#', [ $text_input]);

    //Step 3: implement some form data processing.
    if ($request->getMethod() == "POST") {
        $form = $form->withRequest($request);
        $result = $form->getData();
    } else {
        $result = "No result yet.";
    }

    //Step 4: Render the checkbox with the enclosing form.
    return
        "<pre>" . print_r($result, true) . "</pre><br/>" .
        $renderer->render($form);
}
