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

namespace ILIAS\UI\examples\Input\Field\Hidden;

/**
 * ---
 * description: >
 *   Example show how to create and render a basic hidden input field and
 *   attach it to a form. This example does not contain any data processing.
 *
 * expected output: >
 *   ILIAS shows an empty white field. Clicking "Save" relaods the page.
 * ---
 */
function base()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Step 1: Define the text input field
    $hidden = $ui->input()->field()->hidden()->withValue("csrf_token_or_some_other_persistent_data");

    //Step 2: Define the form and attach the section.
    $form = $ui->input()->container()->form()->standard("#", [$hidden]);

    //Step 4: Render the form with the text input field
    return $renderer->render($form);
}
