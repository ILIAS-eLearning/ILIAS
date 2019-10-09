<?php
/**
 * Example show how to create and render a basic textarea field and attach it to a
 * form.
 */
function base()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $ctrl = $DIC->ctrl();
    $request = $DIC->http()->request();

    //Step 1: Define the textarea input field
    $textarea_input = $ui->input()->field()->textarea("Textarea Input", "Just a textarea input.");

    //Step 2: Define the form and form actions.
    $ctrl->setParameterByClass(
        'ilsystemstyledocumentationgui',
        'example_name',
        'textarea'
    );
    $form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');
    $form = $ui->input()->container()->form()->standard($form_action, [$textarea_input]);

    //Step 3: implement some form data processing.
    if ($request->getMethod() == "POST"
        && $request->getQueryParams()['example_name'] == "textarea") {
        $form = $form->withRequest($request);
        $result = $form->getData();
    } else {
        $result = "No result yet.";
    }

    //Step 4: Render the form with the text input field
    return
        "<pre>" . print_r($result, true) . "</pre><br/>" .
        $renderer->render($form);
}
