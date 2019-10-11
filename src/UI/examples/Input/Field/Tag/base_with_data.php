<?php
/**
 * Example show how to create and render a basic tag input field and attach it to a
 * form. This example does not contain any data processing.
 */
function base_with_data()
{
    // Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    // Step 1: Define the tag input field
    $multi_select_input = $ui->input()->field()->tag(
        "Basic TagInput",
        ['Interesting', 'Boring', 'Animating', 'Repetitious'],
        "Just some tags"
    );

    // Step 2, define form and form actions
    $DIC->ctrl()->setParameterByClass(
        ilSystemStyleDocumentationGUI::class,
        'example_name',
        'tag_inputs'
    );
    $DIC->ctrl()->saveParameterByClass(
        ilSystemStyleDocumentationGUI::class,
        'node_id'
    );

    $form_action = $DIC->ctrl()->getFormActionByClass(ilSystemStyleDocumentationGUI::class);
    $form = $ui->input()->container()->form()->standard($form_action, [$multi_select_input]);

    // Step 4, implement some form data processing.
    if ($request->getMethod() === "POST" && $request->getQueryParams()['example_name'] === 'tag_inputs') {
        $form = $form->withRequest($request);
        $result = $form->getData();
    } else {
        $result = "No result yet.";
    }

    // Return the rendered form
    return "<pre>" . print_r($result, true) . "</pre><br/>" . $renderer->render($form);
}
