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

namespace ILIAS\UI\examples\Input\Field\MultiSelect;

/**
 * ---
 * description: >
 *   Base example showing how to plug a Multi-Select into a form.
 *
 * expected output: >
 *   ILIAS shows a grouped titled "Take your picks" with checkboxes. You can activate one or more checkboxes. After saving
 *   your selection ILIAS will display your selection in the following format:
 *
 *   Array
 *   (
 *      [multi] => Array
 *      (
 *          [0] => 2
 *          [1] => 3
 *      )
 *   )
 *
 *   If you click "Save" without selecting a checkbox ILIAS will display a error message below the group.
 * ---
 */
function base()
{
    //declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    //define options.
    $options = array(
        "1" => "Pick 1",
        "2" => "Pick 2",
        "3" => "Pick 3",
        "4" => "Pick 4",
    );

    //define the select
    $multi = $ui->input()->field()->multiselect("Take your picks", $options, "This is the byline text")
        ->withRequired(true);

    //define form and form actions
    $form = $ui->input()->container()->form()->standard('#', ['multi' => $multi]);


    //implement some form data processing.
    if ($request->getMethod() == "POST") {
        try {
            $form = $form->withRequest($request);
            $result = $form->getData();
        } catch (\InvalidArgumentException $e) {
            $result = "No result. Probably, the other form was used.";
        }
    } else {
        $result = "No result yet.";
    }

    //render the select with the enclosing form.
    return
        "<pre>" . print_r($result, true) . "</pre><br/>" .
        $renderer->render($form);
}
