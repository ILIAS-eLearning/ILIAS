<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Container\Form\Standard;

/**
 * Example show how to create and render a basic form with one input. This example does
 * not contain any data processing.
 */
function base()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Step 1: Define some input field to plug into the form.
    $text_input = $ui->input()->field()->text("Basic Input", "Just some basic input");

    //Step 2: Define some section carrying a title and description with the previously
    //defined input
    $section1 = $ui->input()->field()->section([$text_input], "Section 1", "Description of Section 1");

    //Step 3: Define the form action to target the input processing
    $DIC->ctrl()->setParameterByClass(
        'ilsystemstyledocumentationgui',
        'example_name',
        'base'
    );
    $form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');

    //Step 4: Define the form and attach the section.
    $form = $ui->input()->container()->form()->standard($form_action, [$section1]);

    //Step 5: Render the form
    return $renderer->render($form);
}
