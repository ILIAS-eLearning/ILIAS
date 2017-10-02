<?php
function numeric_inputs() {
    //Step 0, initiate factories
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
	$request = $DIC->http()->request();
    $trafo = new \ILIAS\Transformation\Factory();

    //Step 1, implement transformation
    $sum = $trafo->custom(function($vs) {
        list($l, $r) = $vs;
        $s = $l + $r;
        return "$l + $r = $s";
    });

    //Step 2, define inputs
	$number_input = $ui->input()->field()->numeric("number", "Put in a number.");

    //Step 3, define form and form actions
	$DIC->ctrl()->setParameterByClass(
			'ilsystemstyledocumentationgui',
			'example_name',
			'numeric_inputs'
	);
	$form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');
	$form = $ui->input()->container()->form()->standard($form_action,
		[ $number_input->withLabel("Left")
		, $number_input->withLabel("Right")
		])
		->withAdditionalTransformation($sum);

    //Step 4, implement some form data processing.
	if ($request->getMethod() == "POST"
			&& $request->getQueryParams()['example_name'] =='numeric_inputs') {
		$form = $form->withRequest($request);
		$result = $form->getData();
	}
	else {
		$result = "No result yet.";
	}

    return 
		"<pre>".print_r($result, true)."</pre><br/>".
		$renderer->render($form);
}
