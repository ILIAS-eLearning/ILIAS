<?php
/**
 * Base example showing how to use interval-inputs
 */
function base() {

	//Step 0: Declare dependencies
	global $DIC;

	$ui = $DIC->ui()->factory();
	$data = new ILIAS\Data\Factory();
	$renderer = $DIC->ui()->renderer();
	$request = $DIC->http()->request();
	$ctrl = $DIC->ctrl();

	//Step 1: define the inputs
	$interval = $ui->input()->field()->DateTimeInterval("Define an interval", "This is the byline text");
	$intervalValue = $interval
		->withValue(new \DateInterval('P1DT2H'))
		->withByline('should calc to 26 H');


	//Step 2: define form and form actions
	$ctrl->setParameterByClass(
		'ilsystemstyledocumentationgui',
		'example_name',
		'interval'
	);
	$form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');
	$form = $ui->input()->container()->form()->standard($form_action, [
		'interval'=>$interval,
		'interval2'=>$intervalValue
	]);

	//Step 3: implement some form data processing.
	if ($request->getMethod() == "POST"
		&& $request->getQueryParams()['example_name'] == "interval") {
		$form = $form->withRequest($request);
		$result = $form->getData();
	}
	else {
		$result = "No result yet.";
	}

	//Step 4: Render the form.
	return
		"<pre>".print_r($result, true)."</pre><br/>".
		$renderer->render($form);
}
