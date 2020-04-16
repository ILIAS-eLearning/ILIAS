<?php
/**
 * Example showing how a dependant group (aka sub form) might be attached to a checkbox.
 */
function with_dependant_group()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    //Step 1: Define the dependent group (aka sub section)
    $dependant_field = $ui->input()->field()->text("Item 1", "Just some dependent group field");
    $dependant_group = $ui->input()->field()->dependantGroup(["dependant_field" => $dependant_field]);

    //Step 2: define the checkbox and attach the dependant group
    $checkbox_input = $ui->input()->field()->checkbox("Checkbox", "Check to display dependant field.")
            ->withDependantGroup($dependant_group);

    //Step 3: define form and form actions
    $DIC->ctrl()->setParameterByClass(
        'ilsystemstyledocumentationgui',
        'example_name',
        'dependant_checkbox'
    );
    $form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');
    $form = $ui->input()->container()->form()->standard($form_action, [ $checkbox_input]);

    //Step 4: implement some form data processing. Note, the value of the checkbox will
    // be 'checked' if checked an null if unchecked.
    if ($request->getMethod() == "POST"
        && $request->getQueryParams()['example_name'] == 'dependant_checkbox') {
        $form = $form->withRequest($request);
        $result = $form->getData();
    } else {
        $result = "No result yet.";
    }

    //Step 5: Render the checkbox with the enclosing form.
    return
        "<pre>" . print_r($result, true) . "</pre><br/>" .
        $renderer->render($form);
}
