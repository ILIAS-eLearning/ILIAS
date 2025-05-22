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

namespace ILIAS\UI\examples\Input\Field\Text;

/**
 * ---
 * description: >
 *   Example shows how to create and render a basic text input field with an error
 *   attached to it. This example does not contain any data processing.
 *
 * expected output: >
 *   ILIAS shows a text field titled "Basic Input". You can enter numbers and letters into the field.
 *   Below the field, a color-coded error message "Some error" is displayed.
 *   The error is also marked by a colored line on the left of the field's label.
 * ---
 */
function with_error()
{
    //Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Define the text input field
    $text_input = $ui->input()->field()->text("Basic Input", "Just some basic input
    with some error attached.")
        ->withError("Some error");

    //Define the form and attach the section.
    $form = $ui->input()->container()->form()->standard("#", [$text_input]);

    //Render the form with the text input field
    return $renderer->render($form);
}
