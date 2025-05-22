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

namespace ILIAS\UI\examples\Input\Field\Section;

/**
 * ---
 * description: >
 *   Example showing how sections can be used to attach transformation and constraints on
 *   multiple fields at once. Note that sections have a standard way of displaying
 *   constraint violations to the user.
 *
 * expected output: >
 *   ILIAS shows a section with two input fields and a byline. The section is titled "Equals 10", the input fields are
 *   labeled "Left" and "Right". You can insert numbers. Clicking "Save" will reload the page. If the addition of the
 *   inserted numbers is 10 ILIAS will output a display accordingly. Else an error message "The sum must equal ten" will
 *   be displayed under the fields.
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
    $refinery = new \ILIAS\Refinery\Factory($data, $lng);

    //Step 1: Implement transformation and constraints
    $sum = $refinery->custom()->transformation(function ($vs) {
        list($l, $r) = $vs;
        $s = $l + $r;
        return $s;
    });
    $equal_ten = $refinery->custom()->constraint(function ($v) {
        return $v == 10;
    }, "The sum must equal ten");

    //Step 2: Define inputs
    $number_input = $ui->input()->field()->numeric("number", "Put in a number.");

    //Step 3: Define the group, add the inputs to the group and attach the
    //transformation and constraint
    $group = $ui->input()->field()->section(
        [ $number_input->withLabel("Left"), $number_input->withLabel("Right")],
        "Equals 10",
        "Left and Right must equal 10"
    )
        ->withAdditionalTransformation($sum)
        ->withAdditionalTransformation($equal_ten);

    //Step 3, define form and form actions, attach the group to the form
    $form = $ui->input()->container()->form()->standard('#', [$group]);

    //Step 4, implement some form data processing.
    if ($request->getMethod() == "POST") {
        $form = $form->withRequest($request);
        $result = $form->getData()[0] ?? "";
    } else {
        $result = "No result yet.";
    }

    //Return the rendered form
    return
        "<pre>" . print_r($result, true) . "</pre><br/>" .
        $renderer->render($form);
}
