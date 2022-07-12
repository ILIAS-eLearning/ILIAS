<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field\Hidden;

/**
 * Example show how to create and render a basic hidden input field and attach it to a
 * form. This example does not contain any data processing.
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
