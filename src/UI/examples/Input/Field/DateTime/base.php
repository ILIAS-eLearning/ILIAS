<?php
/**
 * Base example showing how to plug date-inputs into a form
 */
function base() {

	//Step 0: Declare dependencies
	global $DIC;

	$ui = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();
	$request = $DIC->http()->request();
	$ctrl = $DIC->ctrl();


	//Step 1: define the inputs
	$date = $ui->input()->field()->dateTime("Pick a date/time", "This is the byline text");
	$time = $date->withFormat('HH:mm')->withTimeGlyph(true);
	$both = $date->withFormat('DD.MM.YYYY HH:mm');
	$weird = $date->withFormat('dddd, DD. MMM YYYY HH:mm');


	//Step 2: define form and form actions
	$ctrl->setParameterByClass(
		'ilsystemstyledocumentationgui',
		'example_name',
		'date'
	);
	$form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');
	$form = $ui->input()->container()->form()->standard($form_action, [
		'date'=>$date,
		'time'=>$time,
		'both'=>$both,
		'weird'=>$weird
	]);

	//Step 3: implement some form data processing.
	if ($request->getMethod() == "POST"
		&& $request->getQueryParams()['example_name'] == "date") {
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
