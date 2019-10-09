<?php
/**
 * Example show how to create and render a basic text input field with an error
 * attached to it. This example does not contain any data processing.
 */
function with_error()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Step 1: Define the text input field
    $text_input = $ui->input()->field()->text("Basic Input", "Just some basic input
    with some error attached.")
        ->withError("Some error");

    //Step 2: Define the form and attach the section.
    $form = $ui->input()->container()->form()->standard("#", [$text_input]);

    //Step 4: Render the form with the text input field
    return $renderer->render($form);
}
