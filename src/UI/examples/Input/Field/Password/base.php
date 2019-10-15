<?php
/**
 * Example of how to create and render a basic password field and attach it to a form.
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
