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

namespace ILIAS\UI\examples\Input\Field\Group;

/**
 * ---
 * description: >
 *   Example showing how groups can be used to attach transformation and constraints on
 *   multiple fields at once. Note that groups do not have a defined way of outputting
 *   validation errors. This is context dependent.
 *
 * expected output: >
 *   ILIAS shows a group of two input fields titled "Left" and "Right". You can insert numbers. Clicking "Save" reloads
 *   the page. If the addition of both the inserted numbers equals 10 ILIAS will display following:
 *
 *   Array
 *   (
 *      [custom_group] => 10
 *   )
 *
 *   Else the error message "The sum must equal ten." will be displayed.
 * ---
 */
function base()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $lng = $DIC->language();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();
    $data = new \ILIAS\Data\Factory();
    $refinery = $DIC->refinery();

    //Step 1: Implement transformation and constraints
    $sum = $refinery->custom()->transformation(function ($vs) {
        list($l, $r) = $vs;
        $s = $l + $r;
        return $s;
    });
    $equal_ten = $refinery->custom()->constraint(function ($v) {
        return $v == 10;
    }, "The sum must equal ten.");

    //Step 2: Define inputs
    $number_input = $ui->input()->field()->numeric("number", "Put in a number.");

    //Step 3: Define the group, add the inputs to the group and attach the
    //transformation and constraint
    $group = $ui->input()->field()->group(
        [ $number_input->withLabel("Left"), $number_input->withLabel("Right")]
    )
        ->withAdditionalTransformation($sum)
        ->withAdditionalTransformation($equal_ten);

    //Step 4: define form and form actions, attach the group to the form
    $form = $ui->input()->container()->form()->standard('#', ["custom_group" => $group]);

    //Step 4: Implement some form data processing.
    if ($request->getMethod() == "POST") {
        //Step 4.1: Device some context dependant logic to display the potential
        // constraint error on the group.
        $form = $form->withRequest($request);
        $group = $form->getInputs()["custom_group"];
        if ($group->getError()) {
            $result = $group->getError();
        } else {
            //The result is sumarized through the transformation
            $result = $form->getData();
        }
    } else {
        $result = "No result yet.";
    }

    //Step 5: Return the rendered form
    return
        "<pre>" . print_r($result, true) . "</pre><br/>" .
        $renderer->render($form);
}
