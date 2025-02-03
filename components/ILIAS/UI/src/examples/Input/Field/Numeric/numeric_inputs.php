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

namespace ILIAS\UI\examples\Input\Field\Numeric;

/**
 * ---
 * description: >
 *   Base example showing how to plug a numeric input into a form.
 *
 * expected output: >
 *   ILIAS shows two input fields titled "Some Number". One input field already displays a number. The other input field
 *   is required (*). You can enter numbers into the fields or choose a number by using the the arrows at the end of the fields.
 *   Clicking "Save" reloads the page.
 *   Afterwards ILIAS will show the inserted number in the following format:
 *
 *   Array
 *   (
 *       [n1] => 56
 *       [n2] => 77
 *   )
 *
 *   If you insert one or more non-numeric numbers into the field the input field will get highlighted in red. Saving
 *   those inputs results in displaying an error message right above the required field.
 * ---
 */
function numeric_inputs()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    //Step 1: Declare the numeric input
    $number_input = $ui->input()->field()
        ->numeric("Some Number", "Put in a number.")
        ->withValue(133);

    $number_input2 = $number_input->withRequired(true)->withValue('');

    //Step 2, define form and form actions
    $form = $ui->input()->container()->form()->standard('#', [
        'n1' => $number_input,
        'n2' => $number_input2
    ]);

    //Step 3, implement some form data processing.
    if ($request->getMethod() == "POST") {
        $form = $form->withRequest($request);
        $result = $form->getData();
    } else {
        $result = "No result yet.";
    }

    //Return the rendered form
    return
        "<pre>" . print_r($result, true) . "</pre><br/>" .
        $renderer->render($form);
}
