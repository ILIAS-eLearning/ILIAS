<?php
/**
 * Example showing how keys can be used when attaching input fields to a form.
 */
function with_keys()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    //Step 1: Define the input fields
    $number_input = $ui->input()->field()
        ->text("number", "Some numeric input");

    //Step 2: Define the form action to target the input processing
    $DIC->ctrl()->setParameterByClass(
        'ilsystemstyledocumentationgui',
        'example_name',
        'keys'
    );
    $form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');

    //Step 5: Define the form, plugin the inputs and attach some transformation acting
    // on the complete input of the form.
    $form = $ui->input()->container()->form()->standard(
        $form_action,
        [ 'input1' => $number_input->withLabel("Input 1")
        , 'input2' => $number_input->withLabel("Input 2")
        ]
    );

    //Step 6: Define some data processing.
    if ($request->getMethod() == "POST"
            && $request->getQueryParams()['example_name'] == 'keys') {
        $form = $form->withRequest($request);
        $result = $form->getData();
    } else {
        $result = "No result yet.";
    }

    //Step 7: Render the form and the result of the data processing
    return
        "<pre>" . print_r($result, true) . "</pre><br/>" .
        $renderer->render($form);
}
