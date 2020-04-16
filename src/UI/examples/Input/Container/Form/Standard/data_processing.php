<?php
/**
 * Example showing how constraints and transformation can be attached to a form.
 */
function data_processing()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $lng = $DIC->language();
    $trafo = new \ILIAS\Transformation\Factory();
    $data = new \ILIAS\Data\Factory();
    $validation = new \ILIAS\Validation\Factory($data, $lng);
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    //Step 1: Define transformations
    $sum = $trafo->custom(function ($vs) {
        list($l, $r) = $vs;
        $s = $l + $r;
        return "$l + $r = $s";
    });

    $from_name = $trafo->custom(function ($v) {
        switch ($v) {
            case "one": return 1;
            case "two": return 2;
            case "three": return 3;
            case "four": return 4;
            case "five": return 5;
            case "six": return 6;
            case "seven": return 7;
            case "eight": return 8;
            case "nine": return 9;
            case "ten": return 10;
        }
        throw new \LogicException("PANIC!");
    });

    //Step 2: Define custom constraint
    $valid_number = $validation->custom(function ($v) {
        return in_array($v, ["one", "two", "three", "four", "five", "six", "seven", "eight", "nine", "ten"]);
    }, "This is not a number I know...");

    //Step 3: Define the input field and attach the previously defined constraint an
    // validation.
    $number_input = $ui->input()->field()
        ->text("number", "Put in the name of a number from one to ten.")
        ->withAdditionalConstraint($valid_number)
        ->withAdditionalTransformation($from_name);

    //Step 4: Define the form action to target the input processing
    $DIC->ctrl()->setParameterByClass(
        'ilsystemstyledocumentationgui',
        'example_name',
        'data_processing'
    );
    $form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');

    //Step 5: Define the form, plugin the inputs and attach some transformation acting
    // on the complete input of the form.
    $form = $ui->input()->container()->form()->standard(
        $form_action,
        [ $number_input->withLabel("Left")
        , $number_input->withLabel("Right")
        ]
    )
        ->withAdditionalTransformation($sum);

    //Step 6: Define some data processing.
    if ($request->getMethod() == "POST"
            && $request->getQueryParams()['example_name'] == 'data_processing') {
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
