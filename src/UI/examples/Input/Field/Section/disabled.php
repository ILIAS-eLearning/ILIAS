<?php
/**
 * Example showing how disabled sections can be used to attach transformation and constraints on
 * multiple fields at once.
 */
function disabled() {
	//Step 0: Declare dependencies
	global $DIC;
	$ui = $DIC->ui()->factory();
	$lng = $DIC->language();
	$renderer = $DIC->ui()->renderer();
	$request = $DIC->http()->request();
	$data = new \ILIAS\Data\Factory();
	$refinery = new \ILIAS\Refinery\Factory($data, $lng);

	//Step 1: Implement transformation and constraints
	$sum = $refinery->custom()->transformation(function($vs) {
		list($l, $r) = $vs;
		$s = $l + $r;
		return $s;
	});
	$equal_ten = $refinery->custom()->constraint(function($v) {
		return $v==10;
	}, "The sum must equal ten");

	//Step 2: Define inputs
	$number_input = $ui->input()->field()->numeric("number", "Put in a number.")->withValue(5);

	//Step 3: Define the group, add the inputs to the group and attach the
	//transformation and constraint
	$group = $ui->input()->field()->section(
		[ $number_input->withLabel("Left"), $number_input->withLabel("Right")],"Equals 10","Left and Right must equal 10")
		->withAdditionalTransformation($sum)
		->withAdditionalTransformation($equal_ten)
		->withDisabled(true);

	//Step 3, define form and form actions, attach the group to the form
	$DIC->ctrl()->setParameterByClass(
		'ilsystemstyledocumentationgui',
		'example_name',
		'numeric_inputs_disabled'
	);
	$form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');
	$form = $ui->input()->container()->form()->standard($form_action,[$group]);

	//Step 4, implement some form data processing.
	if ($request->getMethod() == "POST"
		&& $request->getQueryParams()['example_name'] =='numeric_inputs_disabled') {
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
