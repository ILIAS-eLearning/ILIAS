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
 *   Base example showing how to plug a radio into a form
 *
 * expected output: >
 *   ILIAS shows a group titled "Radio" with three radio buttons:
 *   - label1
 *   - label2
 *   - label3
 *
 *   You can only activate one radio button at a time. Save your selection. ILIAS will display your selection in the following format:
 *
 *   Array
 *   (
 *       [radio] => value3
 *   )
 * ---
 */
function base()
{
    //Step 1: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    //Step 2: define the radio with options
    $radio = $ui->input()->field()->radio("Radio", "check an option")
        ->withOption('value1', 'label1', 'byline1')
        ->withOption('10', 'numeric value (ten)', 'byline2')
        ->withOption('030', 'not-numeric value', 'byline3');

    //Step 3: define form and form actions
    $form = $ui->input()->container()->form()->standard('#', ['radio' => $radio]);

    //Step 4: implement some form data processing.
    if ($request->getMethod() == "POST") {
        $form = $form->withRequest($request);
        $result = $form->getData();
    } else {
        $result = "No result yet.";
    }

    //Step 5: Render the radio with the enclosing form.
    return
        "<pre>" . print_r($result, true) . "</pre><br/>" .
        $renderer->render($form);
}
