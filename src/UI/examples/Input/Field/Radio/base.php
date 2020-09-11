<?php
/**
 * Base example showing how to plug a radio into a form
 */
function base()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();


    //Step 1: define dependant fields
    $dependant_field1 = $ui->input()->field()->text("Item 1", "Just some dependent group field");
    $dependant_field2 = $ui->input()->field()->text("Item 2", "Just some dependent group field");
    $dependant_fields = [
        "dependant_field_a" => $dependant_field1,
        "dependant_field_b" => $dependant_field2
    ];

    $radio_d = $ui->input()->field()->radio("Dep. Radio", "check an option")
        ->withOption('value1', 'label1', 'byline1')
        ->withOption('value2', 'label2', 'byline2', $dependant_fields);

    $radio_num_value = $ui->input()->field()->radio("Numeric Values", "pick one...")
        ->withOption('1', 'One', '')
        ->withOption('2', 'Two', '')
        ->withOption('3', 'Three', '');

    //Step 2: define the radio
    $radio = $ui->input()->field()->radio("Radio", "check an option")
        ->withOption('value1', 'label1', 'byline1')
        ->withOption('value2', 'label2', 'byline2', $dependant_fields)
        ->withOption('value3', 'label3', 'byline3', [$radio_d])
        ->withOption('value4', 'numerics', 'test num values', [$radio_num_value]);


    //Step 3: define form and form actions
    $DIC->ctrl()->setParameterByClass(
        'ilsystemstyledocumentationgui',
        'example_name',
        'radio'
    );
    $form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');
    $form = $ui->input()->container()->form()->standard('#', ['radio' => $radio]);

    //Step 4: implement some form data processing. Note, the value of the checkbox will
    // be 'checked' if checked an null if unchecked.
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
