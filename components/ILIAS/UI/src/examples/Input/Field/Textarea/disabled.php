<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field\Textarea;

/**
 * Example show how to create and render a disabled textarea field and attach it to a
 * form.
 */
function disabled()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $ctrl = $DIC->ctrl();
    $request = $DIC->http()->request();

    //Step 1: Define the textarea input field
    $textarea_input = $ui->input()->field()->textarea("Disabled Textarea Input", "Just a disabled textarea input.")
        ->withDisabled(true);

    //Step 2: Define the form action to target the input processing
    $DIC->ctrl()->setParameterByClass(
        'ilsystemstyledocumentationgui',
        'example_name',
        'disabled'
    );
    $form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');

    //Step 3: Define the form and form actions.
    $form = $ui->input()->container()->form()->standard($form_action, [$textarea_input]);

    //Step 4: implement some form data processing.
    if ($request->getMethod() == "POST" && $request->getQueryParams()['example_name'] == 'disabled') {
        $form = $form->withRequest($request);
        $result = $form->getData();
    } else {
        $result = "No result yet.";
    }

    //Step 5: Render the form with the text input field
    return
        "<pre>" . print_r($result, true) . "</pre><br/>" .
        $renderer->render($form);
}
