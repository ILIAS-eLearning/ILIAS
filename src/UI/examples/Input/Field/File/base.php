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
    $file_input = $ui->input()->field()->file(new ilUIDemoFileUploadHandlerGUI(), "Upload File", "you can drop your files here");

    //Step 2: Define the form and attach the field.
    $form = $ui->input()->container()->form()->standard("#", [$file_input]);

    //Step 4: Render the form.
    return $renderer->render($form);
}
