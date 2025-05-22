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
 *   Example showing how a dependent group (aka sub form) might be attached to a checkbox.
 *
 * expected output: >
 *   Next to the title "Optional Group" ILIAS displays a checkbox with a byline. If the checkbox is activated two input
 *   fields "Item 1" and "Item 2" are displayed including a byline each. If the ckeckbox is deactivated those two input
 *   fields are not visible.
 *   If you activate the checkbox, fill out the text field and the date field and click "Save" the following output will
 *   be displayed above the box:
 *
 *   Array
 *   (
 *      [0] => Array
 *      (
 *          [dependant_text] => MEINE EINGABE
 *          [dependant_date] => DateTimeImmutable Object
 *          (
 *              [date] => EINGEGEBENES-DATUM-ALS-YYYY-MM-DD 00:00:00.000000
 *              [timezone_type] => 3
 *              [timezone] => Europe/Berlin
 *          )
 *       )
 *   )
 * ---
 */
function base()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    //Step 1: Define the fields in the group
    $dependant_field = $ui->input()->field()->text("Item 1", "Just some dependent group field");
    $dependant_field2 = $ui->input()->field()->datetime("Item 2", "a dependent date");

    //Step 2: define the checkbox and attach the dependant group
    $checkbox_input = $ui->input()->field()->optionalGroup(
        [
            "dependant_text" => $dependant_field,
            "dependant_date" => $dependant_field2
        ],
        "Optional Group",
        "Check to display group field."
    );

    //Step 3: define form and form actions
    $form = $ui->input()->container()->form()->standard('#', [ $checkbox_input]);

    //Step 4: implement some form data processing. Note, the value of the checkbox will
    // be 'checked' if checked an null if unchecked.
    if ($request->getMethod() == "POST") {
        $form = $form->withRequest($request);
        $result = $form->getData();
    } else {
        $result = "No result yet.";
    }

    //Step 5: Render the checkbox with the enclosing form.
    return
        "<pre>" . print_r($result, true) . "</pre><br/>" .
        $renderer->render($form);
}
