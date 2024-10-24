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

namespace ILIAS\UI\examples\Input\Field\Select;

/**
 * ---
 * description: >
 *   Example showing how to plug a disabled Select into a form
 *
 * expected output: >
 *   ILIAS shows a selection button but it's disabled. You can not select any option.
 * ---
 */
function disabled()
{
    //Step 0: Declare dependencies
    global $DIC;

    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();
    $ctrl = $DIC->ctrl();

    //Define the options.
    $options = array(
        "1" => "Type 1",
        "2" => "Type 2",
        "3" => "Type 3",
        "4" => "Type 4",
    );

    //Step 1: define the select
    $select = $ui->input()->field()->select("Cannot choose an Option", $options, "This is the byline text")
        ->withDisabled(true);

    //Step 2: define form and form actions
    $form = $ui->input()->container()->form()->standard('#', [$select]);

    //Step 3: implement some form data processing.
    if ($request->getMethod() == "POST") {
        $form = $form->withRequest($request);
        $result = $form->getData();
    } else {
        $result = "No result yet.";
    }

    //Step 4: Render the select with the enclosing form.
    return
        "<pre>" . print_r($result, true) . "</pre><br/>" .
        $renderer->render($form);
}
