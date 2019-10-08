<?php
/**
 * Example showing how to plug a disabled checkbox into a form
 */
function disabled()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    //Step 1: define the checkbox, and making it disabled
    $checkbox_input = $ui->input()->field()->checkbox("Checkbox", "Cannot check.")
        ->withDisabled(true);

    //Step 2: define form and form actions
    $DIC->ctrl()->setParameterByClass(
        'ilsystemstyledocumentationgui',
        'example_name',
        'checkbox_disabled'
    );
    $form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');
    $form = $ui->input()->container()->form()->standard($form_action, [ $checkbox_input]);

    //Step 3: implement some form data processing. Note, the value of the checkbox will
    // be 'checked' if checked and null if unchecked.
    if ($request->getMethod() == "POST"
        && $request->getQueryParams()['example_name'] =='checkbox_disabled') {
        $form = $form->withRequest($request);
        $result = $form->getData();
    } else {
        $result = "No result yet.";
    }

    //Step 4: Render the checkbox with the enclosing form.
    return
        "<pre>" . print_r($result, true) . "</pre><br/>" .
        $renderer->render($form);
}
