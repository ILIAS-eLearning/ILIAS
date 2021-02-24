<?php
/**
 * Example show how to create and render a basic tag input field and attach it to a
 * form. This example does not contain any data processing.
 */
function base_with_value()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Step 1: Define the tag input field
    $multi_select_input = $ui->input()->field()->tag(
        "Basic TagInput",
        ['Interesting', 'Boring', 'Animating', 'Repetitious'],
        "Just some tags"
    )->withValue(["Interesting"]);

    //Step 2, define form and form actions
    $form = $ui->input()->container()->form()->standard("#", [$multi_select_input]);

    //Return the rendered form
    return  $renderer->render($form);
}
