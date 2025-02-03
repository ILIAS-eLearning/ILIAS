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
 *   ILIAS shows an input field titled "Basic TagInput". The Tag "Interesting" is already displayed and can get removed
 *   through clicking the "X". A completion of the tags will be displayed by ILIAS if an A, B, I or R is typed into the field.
 *   It is also possible to insert tags of your own and confirm those through hitting the Enter button on your keyboard.
 *   Afterwards the tags will be highlighted with color. An "X" is displayed directly next to each tag. Clicking the "X"
 *   will remove the tag.
 *   Clicking "Save" will reload the page and will set the Tag in the input field back to "Interesting".
 * ---
 */
function base_with_value()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Step 1: Define the tag input field
    $tag_input = $ui->input()->field()->tag(
        "Basic TagInput",
        ['Interesting', 'Boring', 'Animating', 'Repetitious'],
        "Just some tags"
    )->withValue(["Interesting"]);

    //Step 2, define form and form actions
    $form = $ui->input()->container()->form()->standard("#", [$tag_input]);

    //Return the rendered form
    return  $renderer->render($form);
}
