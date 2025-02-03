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

namespace ILIAS\UI\examples\Input\Field\Tag;

/**
 * ---
 * description: >
 *   The example shows how to create and render a basic tag input field and attach it to a
 *   form. This example does not contain any data processing.
 *
 * expected output: >
 *   ILIAS shows an input field titled "Basic TagInput". ILIAS will display a completion of the tags if an A, B, I or R
 *   is typed in. It is also possible to insert tags of your own and confirm those through hitting the Enter button on your
 *   keyboard. Afterwards the tags get highlighted with color. An "X" is positioned directly next to each tag. Removing
 *   a tag is possible through clicking the "X".
 *   Clicking "Save" reloads the page and displays your input in the following format above the input field:
 *
 *   Array
 *   (
 *      [0] => Array
 *      (
 *          [0] => Interesting
 *          [1] => Animating
 *          [2] => Whatever
 *      )
 *   )
 * ---
 */
function base_with_data()
{
    // Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    // Step 1: Define the tag input field
    $tag_input = $ui->input()->field()->tag(
        "Basic TagInput",
        ['Interesting & fascinating', 'Boring, dull', 'Animating', 'Repetitious'],
        "Just some tags"
    );

    // Step 2, define form and form actions
    $form = $ui->input()->container()->form()->standard('#', ['f2' => $tag_input]);

    // Step 3, implement some form data processing.
    if ($request->getMethod() === "POST") {
        $form = $form->withRequest($request);
        $result = $form->getData();
    } else {
        $result = "No result yet.";
    }

    // Step 4, return the rendered form with data
    return "<pre>"
        . print_r($result, true)
        . "</pre><br/>"
        . $renderer->render($form);
}
