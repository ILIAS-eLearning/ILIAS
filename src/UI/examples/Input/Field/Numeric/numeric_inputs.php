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
    $number_input = $ui->input()->field()
        ->numeric("Some Number", "Put in a number.")
        ->withValue(133);

    $number_input2 = $number_input->withRequired(true)->withValue('');

    //Step 2, define form
    $form = $ui->input()->container()->form()->standard('#', [
        'n1' => $number_input,
        'n2' => $number_input2
    ]);

    //Step 3, implement some form data processing.
    if ($request->getMethod() == "POST") {
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
