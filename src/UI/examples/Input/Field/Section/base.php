<?php
function base() {
    //Step 0, initiate factories
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();
    $data = new \ILIAS\Data\Factory();
    $validation = new \ILIAS\Validation\Factory($data);
    $trafo = new \ILIAS\Transformation\Factory();

    //Step 1, implement transformation and constraints
    $sum = $trafo->custom(function($vs) {
        list($l, $r) = $vs;
        $s = $l + $r;
        return $s;
    });
    
    $equal_ten = $validation->custom(function($v) {
        return $v==10;
    }, "The sum must equal ten");

    //Step 2, define inputs
    $number_input = $ui->input()->field()->numeric("number", "Put in a number.");

    //Step 3, define the group, add the inputs to the group and attach the
    //transformation and constraint
    $group = $ui->input()->field()->section(
        [ $number_input->withLabel("Left"), $number_input->withLabel("Right")],"Equals 10","Left and Right must equal 10")
        ->withAdditionalTransformation($sum)
        ->withAdditionalConstraint($equal_ten);

    //Step 3, define form and form actions, attach the group to the form
    $DIC->ctrl()->setParameterByClass(
        'ilsystemstyledocumentationgui',
        'example_name',
        'numeric_inputs'
    );
    $form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');
    $form = $ui->input()->container()->form()->standard($form_action,[$group]);

    //Step 4, implement some form data processing.
    if ($request->getMethod() == "POST"
        && $request->getQueryParams()['example_name'] =='numeric_inputs') {
        $form = $form->withRequest($request);
        $result = $form->getData()[0];
    }
    else {
        $result = "No result yet.";
    }

    //Return the rendered form
    return
        "<pre>".print_r($result, true)."</pre><br/>".
        $renderer->render($form);
}
