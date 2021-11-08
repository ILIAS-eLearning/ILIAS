<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field\File;

/**
 * Example of how to create and render a file input field and attach it to a form.
 */
function base()
{
    // Step 0: Declare dependencies.
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    // Step 1: Define the input field.
    // See the implementation of a UploadHandler in Services/UI/classes/class.ilUIDemoFileUploadHandlerGUI.php
    $file_input = $ui->input()->field()->file(new \ilUIDemoFileUploadHandlerGUI(), "Upload File", "you can drop your files here");

    // Step 2: Define the form and attach the field.
    $form = $ui->input()->container()->form()->standard("#", [$file_input]);

    // Step 4: Render the form.
    return $renderer->render($form);
}
