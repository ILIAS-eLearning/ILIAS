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
 *   Example showing how to use a numeric input with decimals.
 *
 * expected output: >
 *   ILIAS shows four numric input fields.
 *   You can enter numbers into the fields or choose a number by using the the arrows at the end of the fields.
 *   Operation the arrows will in-/decrease the value by the given step size.
 *   Clicking "Save" reloads the page.
 *   Afterwards ILIAS will show the inserted number in the following format:
 *
 *   Array
 *   (
 *       [0] => 3 (integer)
 *       [1] => 0.4 (double)
 *       [2] => 0.1 (double)
 *       [3] => 10.7 (double)
 *   )
 *
 *   If you insert one or more non-numeric numbers into the field the input field will get highlighted in red. Saving
 *   those inputs results in displaying an error message right above the required field.
 * ---
 */
function numeric_with_decimals()
{
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();
    $refinery = $DIC->refinery();

    $number_input = $ui->input()->field()
        ->numeric("int", "step size is 3")
        ->withStepSize(3)
        ->withValue(3);

    $number_input2 = $ui->input()->field()
        ->numeric("float", "step size is .2")
        ->withStepSize(.2)
        ->withValue(.4);

    $number_input3 = $ui->input()->field()
        ->numeric("float", "step size is .0005")
        ->withStepSize(.0005)
        ->withValue(.1);

    $number_input4 = $ui->input()->field()
        ->numeric("float", "step size is 111.01, initial value is 10.7")
        ->withStepSize(111.01)
        ->withValue(10.7);

    $DIC->ctrl()->setParameterByClass(
        'ilsystemstyledocumentationgui',
        'example_name',
        'decimals'
    );
    $form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');
    $form = $ui->input()->container()->form()->standard(
        $form_action,
        [$number_input, $number_input2, $number_input3, $number_input4]
    )
    ->withAdditionalTransformation(
        $refinery->custom()->transformation(
            fn($v) => array_map(fn($val) => $val . ' (' . gettype($val) . ')', $v)
        )
    );

    if ($request->getMethod() == "POST"
        && array_key_exists('example_name', $request->getQueryParams())
        && $request->getQueryParams()['example_name'] == 'decimals') {
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
