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

namespace ILIAS\UI\examples\Input\Container\Form\Standard;

/**
 * ---
 * description: >
 *   Example showing how constraints and transformation can be attached to a form.
 *
 * expected output: >
 *   ILIAS shows a section with two input fields titled "Left" and "Right".
 *   Please enter an advertised number in English between 1 and 10 (e.g. "two" and "four").
 *   ILIAS will add those numbers after the input was saved. The output will be displayed in the following format:
 *   2 + 4 = 6
 *   Entering invalid inputs will result in an error message below the input fields.
 * ---
 */
function data_processing()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();
    $refinery = $DIC->refinery();
    //Step 1: Define transformations
    $sum = $refinery->custom()->transformation(function ($vs) {
        list($l, $r) = $vs;
        $s = $l + $r;
        return "$l + $r = $s";
    });

    $from_name = $refinery->custom()->transformation(function ($v) {
        switch ($v) {
            case "one": return 1;
            case "two": return 2;
            case "three": return 3;
            case "four": return 4;
            case "five": return 5;
            case "six": return 6;
            case "seven": return 7;
            case "eight": return 8;
            case "nine": return 9;
            case "ten": return 10;
        }
        throw new \LogicException("PANIC!");
    });

    //Step 2: Define custom constraint
    $valid_number = $refinery->custom()->constraint(function ($v) {
        return in_array($v, ["one", "two", "three", "four", "five", "six", "seven", "eight", "nine", "ten"]);
    }, "This is not a number I know...");

    //Step 3: Define the input field and attach the previously defined constraint an
    // validation.
    $number_input = $ui->input()->field()
        ->text("number", "Put in the name of a number from one to ten.")
        ->withAdditionalTransformation($valid_number)
        ->withAdditionalTransformation($from_name);

    //Step 4: Define the form action to target the input processing
    $DIC->ctrl()->setParameterByClass(
        'ilsystemstyledocumentationgui',
        'example_name',
        'data_processing'
    );
    $form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');

    //Step 5: Define the form, plugin the inputs and attach some transformation acting
    // on the complete input of the form.
    $form = $ui->input()->container()->form()->standard(
        $form_action,
        [ $number_input->withLabel("Left")
        , $number_input->withLabel("Right")
        ]
    )->withAdditionalTransformation($sum);

    //Step 6: Define some data processing.
    if ($request->getMethod() == "POST"
            && array_key_exists('example_name', $request->getQueryParams())
            && $request->getQueryParams()['example_name'] == 'data_processing') {
        $form = $form->withRequest($request);
        $result = $form->getData();
    } else {
        $result = "No result yet.";
    }

    //Step 7: Render the form and the result of the data processing
    return
        "<pre>" . print_r($result, true) . "</pre><br/>" .
        $renderer->render($form);
}
