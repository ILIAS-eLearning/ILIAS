<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field\Url;

/**
 * This example shows how to create and render a basic URL input field with an error and
 * attach to it. It does not contain any data processing.
 */
function with_error()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Step 1: Define the URL input field
    $url_input = $ui->input()->field()->url("Basic Input", "Just some basic input with 
    some error attached.")
        ->withError("Some error");

    //Step 2: Define the form and attach the section
    $form = $ui->input()->container()->form()->standard("#", [$url_input]);

    //Step 4: Render the form with the URL input field
    return $renderer->render($form);
}
