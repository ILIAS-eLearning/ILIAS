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
 *   This example shows how to create and render a basic URL input field with an value
 *   attached to it. It does also contain data processing.
 *
 * expected output: >
 *   ILIAS shows an input field titled "Basic Input". The field is pre-filled with the text "https://www.iliasd.de/".
 *   If you enter a URL and click "Save" your input should be displayed in the field. If you enter a random text (no URL)
 *   and save your input an error message should be displayed (There is some error in this part.).
 * ---
 */
function with_value()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    //Step 1: Define the URL input field and attach some default value
    $url_input = $ui->input()->field()->url("Basic Input", "Just some basic input with 
    some default url value.")
        ->withValue("https://www.ilias.de/");

    //Step 2: Define the form and attach the section
    $form = $ui->input()->container()->form()->standard("#", [$url_input]);

    //Step 3: Define some data processing
    if ($request->getMethod() == "POST") {
        $form = $form->withRequest($request);
        $result = $form->getData()[0] ?? "";
    } else {
        $result = "No result yet.";
    }

    //Step 4: Render the form with the URL input field
    return
        "<pre>" . print_r($result, true) . "</pre><br />" .
        $renderer->render($form);
}
