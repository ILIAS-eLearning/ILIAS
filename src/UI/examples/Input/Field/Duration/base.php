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


	//Step 1: define the input
	$duration = $ui->input()->field()->duration("Pick a time-span", "This is the byline text");
	$time = $duration->withTimeOnly(true)->withRequired(true);
	$timezone = $duration
		->withTimezone('America/El_Salvador')
		->withUseTime(true)
		->withByline('timezone and both time and date');

	//Step 2: define form and form actions, attach the input
	$ctrl->setParameterByClass(
		'ilsystemstyledocumentationgui',
		'example_name',
		'duration'
	);
	$form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');
	$form = $ui->input()->container()->form()->standard(
		$form_action,
		[
			'duration'=>$duration,
			'time'=>$time,
			'timezone'=>$timezone
		]
	);

	//Step 3: implement some form data processing.
	if ($request->getMethod() == "POST"
		&& $request->getQueryParams()['example_name'] == "duration") {
		$form = $form->withRequest($request);
		$groups = $form->getInputs();
		foreach ($groups as $group) {

			if($group->getError()){
				$result = $group->getError();
			}else{
				//The result is sumarized through the transformation
				$result = $form->getData();
			}
		};
	}
	else {
		$result = "No result yet.";
	}

	//Step 4: Render the form.
	return
		"<pre>".print_r($result, true)."</pre><br/>".
		$renderer->render($form);
}
