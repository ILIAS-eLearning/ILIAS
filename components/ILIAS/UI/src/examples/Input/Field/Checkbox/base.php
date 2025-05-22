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

namespace ILIAS\UI\examples\Input\Field\Checkbox;

/**
 * ---
 * description: >
 *   Base example showing how to plug a checkbox into a form.
 *
 * expected output: >
 *   ILIAS shows a section with an active checkbox. The checkbox can get deactivated. After saving the status of the
 *   checkbox it will be displayed above (active = 1).
 * ---
 */
function base()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    //Step 1: define the checkbox, and turning it on
    $checkbox_input = $ui->input()->field()->checkbox("Checkbox", "Check or not.")
            ->withValue(true);

    //Step 2: define form and form actions
    $form = $ui->input()->container()->form()->standard('#', [ $checkbox_input]);

    //Step 3: implement some form data processing. Note, the value of the checkbox will
    // be 'checked' if checked an null if unchecked.
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
