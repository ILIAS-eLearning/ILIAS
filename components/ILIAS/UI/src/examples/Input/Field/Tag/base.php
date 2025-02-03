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
 *   ILIAS shows an input field titled "Basic Tag". If typing an A, B, I or R into the field ILIAS will display a
 *   completion of the possible Tags. It is also possible to insert Tags of your own and confirm those through hitting the
 *   Enter button on your keyboard. Tags which are inserted and confirmed will be highlighted with a color. A "X" is positioned
 *   directly next to each tag. You can remove the Tag through clicking the "X".
 * ---
 */
function base()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Step 1: Define the tag input field
    $tag_input = $ui->input()->field()->tag(
        "Basic Tag",
        ['Interesting', 'Boring', 'Animating', 'Repetitious'],
        "Just some tags"
    );


    //Step 2: Define the form and attach the section.
    $form = $ui->input()->container()->form()->standard("#", [$tag_input]);

    //Step 3: Render the form with the text input field
    return $renderer->render($form);
}
