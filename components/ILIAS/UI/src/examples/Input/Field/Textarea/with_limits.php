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

namespace ILIAS\UI\examples\Input\Field\Textarea;

/**
 * ---
 * description: >
 *   This example shows how to create and render a basic textarea field with
 *   minimum and maximum number of characters limit.
 *
 * expected output: >
 *   ILIAS shows an input field titled "Textarea Input". You can enter a text with 20 letters max. Try entering the following
 *   text: "Test text with more than 20 letters". You cannot enter more letters/words after "Test text with more" and
 *   the counter "Characters remaining" is set to 0. After saving the input ILIAS will display your text in the following format:
 *
 *   Array
 *   (
 *       [0] => Test text with more
 *   )
 * ---
 */
function with_limits()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $ctrl = $DIC->ctrl();
    $request = $DIC->http()->request();

    $min_limit = 3;
    $max_limit = 20;

    //Step 1: Define the textarea input field
    $textarea_input = $ui->input()->field()->textarea("Textarea Input", "Just a textarea input.")->withMinLimit($min_limit)->withMaxLimit($max_limit);

    //Step 2: Define the form action to target the input processing
    $DIC->ctrl()->setParameterByClass(
        'ilsystemstyledocumentationgui',
        'example_name',
        'with_limits'
    );
    $form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');

    //Step 3: Define the form and form actions.
    $form = $ui->input()->container()->form()->standard($form_action, [$textarea_input]);

    //Step 4: implement some form data processing.
    if ($request->getMethod() == "POST" && $request->getQueryParams()['example_name'] == 'with_limits') {
        $form = $form->withRequest($request);
        $result = $form->getData();
    } else {
        $result = "No result yet.";
    }

    //Step 5: Render the form with the text input field
    return
        "<pre>" . print_r($result, true) . "</pre><br/>" .
        $renderer->render($form);
}
