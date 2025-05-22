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
 *   Example shows how to create and render a basic text input field with an value
 *   attached to it. This example does not contain any data processing.
 *
 * expected output: >
 *   ILIAS shows a text field titled "Basic Input". The field is already filled with the text "Default Value". You can
 *   enter numbers and letter into the field.
 * ---
 */
function with_value()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Step 1: Define the text input field and attach some default value
    $text_input = $ui->input()->field()->text("Basic Input", "Just some basic input
    with some default value.")
        ->withValue("Default Value");

    //Step 2: Define the form and attach the section.
    $form = $ui->input()->container()->form()->standard("#", [$text_input]);

    //Step 4: Render the form with the text input field
    return $renderer->render($form);
}
