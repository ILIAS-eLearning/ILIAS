<?php
/**
 * Base example showing how to plug a radio into a form
 */
function base()
{
    //Step 1: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    //Step 2: define the radio with options
    $radio = $ui->input()->field()->radio("Radio", "check an option")
        ->withOption('value1', 'label1', 'byline1')
        ->withOption('10', 'numeric value (ten)', 'byline2')
        ->withOption('030', 'not-numeric value', 'byline3');

    //Step 3: define form and form actions
    $form = $ui->input()->container()->form()->standard('#', ['radio' => $radio]);

    //Step 4: implement some form data processing.
    if ($request->getMethod() == "POST") {
        $form = $form->withRequest($request);
        $result = $form->getData();
    } else {
        $result = "No result yet.";
    }

    //Step 5: Render the radio with the enclosing form.
    return
        "<pre>" . print_r($result, true) . "</pre><br/>" .
        $renderer->render($form);
}
