<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field\Url;

/**
 * This example shows how to create and render a disabled URL input field and attach it to a form.
 * It does not contain any data processing.
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
