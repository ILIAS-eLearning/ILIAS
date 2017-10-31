<?php
function base() {
	//Step 0, initiate factories
	global $DIC;
	$ui = $DIC->ui()->factory();
	$trafo = new \ILIAS\Transformation\Factory();

	$renderer = $DIC->ui()->renderer();
	$request = $DIC->http()->request();

	//Step 1, define sub section
	$part1 = $ui->input()->field()->text("Item 1", "Just some sub section item");
	$part2 = $ui->input()->field()->text("Item 2", "Just another sub section item");

	$sub_section = $ui->input()->field()->dependantGroup([ "Sub Part 1"=>$part1, "Sub Part 2"=>$part2]);

	//Step 2, define input and attach sub section
	$checkbox_input = $ui->input()->field()->checkbox("Checkbox", "Check or not.")->withValue(true)
			->withDependantGroup($sub_section);

	//Step 3, define form and form actions
	$DIC->ctrl()->setParameterByClass(
		'ilsystemstyledocumentationgui',
		'example_name',
		'checkbox'
	);

	$form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');
	$form = $ui->input()->container()->form()->standard($form_action, [ "checkbox"=>$checkbox_input]);


	//Step 4, implement some form data processing.
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
