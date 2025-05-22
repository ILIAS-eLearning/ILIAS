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

namespace ILIAS\UI\examples\Input\Field\Radio;

/**
 * ---
 * description: >
 *   Example showing how to plug a disabled radio into a form
 *
 * expected output: >
 *   ILIAS shows a group titled "Radio" with three radio buttons:
 *    - label1
 *    - label2
 *    - label3
 *
 *   You cannot select any options as the radio buttons are disabled.
 * ---
 */
function disabled()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();


    //Step 1: define the radio
    $radio = $ui->input()->field()->radio("Radio", "Cannot check an option")
        ->withOption('value1', 'label1')
        ->withOption('value2', 'label2')
        ->withOption('value3', 'label3')
        ->withDisabled(true);


    //Step 2: define form and form actions
    $form = $ui->input()->container()->form()->standard('#', ['radio' => $radio]);

    //Step 3: implement some form data processing. Note, the value of the checkbox will
    // be 'checked' if checked and null if unchecked.
    if ($request->getMethod() == "POST") {
        $form = $form->withRequest($request);
        $result = $form->getData();
    } else {
        $result = "No result yet.";
    }

    //Step 4: Render the radio with the enclosing form.
    return
        "<pre>" . print_r($result, true) . "</pre><br/>" .
        $renderer->render($form);
}
