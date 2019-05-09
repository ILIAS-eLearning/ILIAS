<?php
/**
 * Example showing how disabled groups can be used.
 */
function disabled() {
	//Step 0: Declare dependencies
	global $DIC;
	$ui = $DIC->ui()->factory();
	$lng = $DIC->language();
	$renderer = $DIC->ui()->renderer();
	$request = $DIC->http()->request();
	$data = new \ILIAS\Data\Factory();
	$validation = new \ILIAS\Refinery\Validation\Factory($data, $lng);
	$trafo = new \ILIAS\Refinery\Transformation\Factory();

	//Step 1: Implement transformation and constraints
	$sum = $trafo->custom(function($vs) {
		list($l, $r) = $vs;
		$s = $l + $r;
		return $s;
	});
	$equal_ten = $validation->custom(function($v) {
		return $v==10;
	}, "The sum must equal ten.");

	//Step 2: Define inputs
	$number_input = $ui->input()->field()->numeric("number", "Cannot put in a number.")->withValue(5);

	//Step 3: Define the group, add the inputs to the group and attach the
	//transformation and constraint
	$group = $ui->input()->field()->group(
		[ $number_input->withLabel("Left"), $number_input->withLabel("Right")])->withDisabled(true)
		->withAdditionalTransformation($sum)
		->withAdditionalConstraint($equal_ten);

	//Step 4: define form and form actions, attach the group to the form
	$DIC->ctrl()->setParameterByClass(
		'ilsystemstyledocumentationgui',
		'example_name',
		'numeric_inputs_disabled'
	);
	$form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');
	$form = $ui->input()->container()->form()->standard($form_action, ["custom_group"=>$group]);

	//Step 4: Implement some form data processing.
	if ($request->getMethod() == "POST"
		&& $request->getQueryParams()['example_name'] =='numeric_inputs_disabled') {
		//Step 4.1: Device some context dependant logic to display the potential
		// constraint error on the group.
		$form = $form->withRequest($request);
		$group = $form->getInputs()["custom_group"];
		if($group->getError()){
			$result = $group->getError();
		}else{
			//The result is sumarized through the transformation
			$result = $form->getData();
		}

	}
	else {
		$result = "No result yet.";
	}

	//Step 5: Return the rendered form
	return
		"<pre>".print_r($result, true)."</pre><br/>".
		$renderer->render($form);
}
