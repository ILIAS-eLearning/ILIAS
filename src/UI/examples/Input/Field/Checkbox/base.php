<?php
function base() {
	//Step 0, initiate factories
	global $DIC;
	$ui = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();
	$request = $DIC->http()->request();

	//Step 1, define inputs
	$checkbox_input = $ui->input()->field()->checkbox("Checkbox", "Check or not.");

	//Step 2, define form and form actions
	$DIC->ctrl()->setParameterByClass(
		'ilsystemstyledocumentationgui',
		'example_name',
		'checkbox'
	);
	$form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');
	$form = $ui->input()->container()->form()->standard($form_action, [ $checkbox_input]);

	//Step 3, implement some form data processing.
	if ($request->getMethod() == "POST"
		&& $request->getQueryParams()['example_name'] =='checkbox') {
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
