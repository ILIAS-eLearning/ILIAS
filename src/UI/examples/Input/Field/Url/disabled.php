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

namespace ILIAS\UI\examples\Input\Field\Url;

/**
 * ---
 * description: >
 *   This example shows how to create and render a disabled URL input field and attach it to a form.
 *   It does not contain any data processing.
 *
 * expected output: >
 *   ILIAS shows an input field titled "Disabled Input". You can not activate the field and therefore you can also not
 *   enter any text. Clicking "Save" will relaod the page.
 * ---
 */
function disabled()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Step 1: Define the URL input field
    $url_input = $ui->input()->field()->url("Disabled Input", "Just some disabled input")->withDisabled(true);

    //Step 2: Define the form and attach the section
    $form = $ui->input()->container()->form()->standard("#", [$url_input]);

    //Step 4: Render the form with the URL input field
    return $renderer->render($form);
}
