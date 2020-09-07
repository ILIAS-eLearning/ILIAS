<?php
/**
 * Base example showing how to plug a numeric input into a form
 */
function numeric_inputs()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    //Step 1: Declare the numeric input
    $number_input = $ui->input()->field()->numeric("Some Number", "Put in a number.")->withValue(133);

    //Step 2, define form and form actions
    $DIC->ctrl()->setParameterByClass(
        'ilsystemstyledocumentationgui',
        'example_name',
        'numeric_inputs'
    );
    $form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');
    $form = $ui->input()->container()->form()->standard($form_action, [ $number_input]);

    //Step 4, implement some form data processing.
    if ($request->getMethod() == "POST"
            && $request->getQueryParams()['example_name'] == 'numeric_inputs') {
        $form = $form->withRequest($request);
        $result = $form->getData();
    } else {
        $result = "No result yet.";
    }

    //Return the rendered form
    return
        "<pre>" . print_r($result, true) . "</pre><br/>" .
        $renderer->render($form);
}
