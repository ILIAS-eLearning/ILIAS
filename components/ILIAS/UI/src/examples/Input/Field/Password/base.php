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

namespace ILIAS\UI\examples\Input\Field\Password;

/**
 * ---
 * description: >
 *   Example of how to create and render a basic password field and attach it to a form.
 *
 * expected output: >
 *   ILIAS shows a input field titled "Password". An inserted text won't be displayed but exchanged with dots.
 * ---
 */
function base()
{
    //Step 0: Declare dependencies.
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Step 1: Define the input field.
    $pwd_input = $ui->input()->field()->password("Password", "enter your password here");

    //Step 2: Define the form and attach the field.
    $form = $ui->input()->container()->form()->standard("#", [$pwd_input]);

    //Step 4: Render the form.
    return $renderer->render($form);
}
