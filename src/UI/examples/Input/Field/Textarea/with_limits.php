<?php
/**
 * This example shows how to create and render a basic textarea field with minimum and maximum number of characters limit.
 * the input is attached to a form.
 */
function with_limits()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $ctrl = $DIC->ctrl();
    $request = $DIC->http()->request();

    $min_limit = 3;
    $max_limit = 20;

    //Step 1: Define the textarea input field
    $textarea_input = $ui->input()->field()->textarea("Textarea Input", "Just a textarea input.")->withMinLimit($min_limit)->withMaxLimit($max_limit);

    //Step 2: Define the form and form actions.
    $ctrl->setParameterByClass(
        'ilsystemstyledocumentationgui',
        'example_name_limited',
        'textarea_limited'
    );
    $form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');
    $form = $ui->input()->container()->form()->standard($form_action, [$textarea_input]);

    //Step 3: implement some form data processing.
    if ($request->getMethod() == "POST"
        && $request->getQueryParams()['example_name_limited'] == "textarea_limited") {
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
