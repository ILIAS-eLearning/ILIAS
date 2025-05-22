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
 *   Example showing how keys can be used when attaching input fields to a form.
 *
 * expected output: >
 *   ILIAS shows a section with two input fields titled "Input 1" and "Input 2". You can enter anything you want.
 *   Your input will be displayed in the following format after being saved:
 *
 *   Array
 *   (
 *      [Input1] => Some input 1
 *      [Innput2] => Some input 2
 *   )
 * ---
 */
function with_keys()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    //Step 1: Define the input fields
    $some_input = $ui->input()->field()
        ->text("Input", "Any Input");

    //Step 2: Define the form action to target the input processing
    $DIC->ctrl()->setParameterByClass(
        'ilsystemstyledocumentationgui',
        'example_name',
        'keys'
    );
    $form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');

    //Step 5: Define the form, plugin the inputs and attach some transformation acting
    // on the complete input of the form.
    $form = $ui->input()->container()->form()->standard(
        $form_action,
        [ 'input1' => $some_input->withLabel("Input 1")
        , 'input2' => $some_input->withLabel("Input 2")
        ]
    );

    //Step 6: Define some data processing.
    if ($request->getMethod() == "POST"
            && array_key_exists('example_name', $request->getQueryParams())
            && $request->getQueryParams()['example_name'] == 'keys') {
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
