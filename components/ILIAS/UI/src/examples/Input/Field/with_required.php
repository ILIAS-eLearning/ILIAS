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
 * ---
 * description: >
 *   Example showing the use of the withRequired() method
 *
 * expected output: >
 *   ILIAS shows the rendered Component.
 * ---
 */
function with_required()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    // Step 1: define the text field and make it a required field,
    // i.e. checking for its default requirement constraint
    // (for text fields: value must have a minimum length of 1)
    $text_input = $ui->input()->field()->text("Enter a name", "And make it a good one!");
    $text_input = $text_input->withRequired(true);

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
