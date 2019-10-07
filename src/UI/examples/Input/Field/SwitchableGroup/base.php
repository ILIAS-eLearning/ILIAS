<?php
/**
 * Example showing how a dependant group (aka sub form) might be attached to a radio.
 */
function base() {
	//Step 0: Declare dependencies
	global $DIC;
	$ui = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();
	$request = $DIC->http()->request();

	//Step 1: define the groups (with their fields and a label each)
	$group1 = $ui->input()->field()->group(
		[
			"field_1_1" => $ui->input()->field()->text("Item 1.1", "Just some field"),
			"field_1_2" => $ui->input()->field()->text("Item 1.2", "Just some other field")
		],
		"switchable group number one"
	);
	$group2 = $ui->input()->field()->group(
		[
			"field_2_1"=>$ui->input()->field()->text("Item 2", "Just another field")
				->withValue('some val')
		],
		"switchable group number two"
	);

	//Step 2: Switchable Group - one or the other:
	$sg = $ui->input()->field()->switchableGroup(
		[
			"g1"=>$group1,
			"g2"=>$group2
		],
		"pick one",
		"...or the other"
	)->withValue("g2");

	//Step 3: define form and form actions
	$DIC->ctrl()->setParameterByClass(
		'ilsystemstyledocumentationgui',
		'example_name',
		'switchablegroup'
	);
	$form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');
	$form = $ui->input()->container()->form()->standard(
		$form_action,
		[
			'switchable_group' => $sg
		]);

	//Step 4: implement some form data processing.
	if ($request->getMethod() == "POST"
		&& $request->getQueryParams()['example_name'] =='switchablegroup') {
		$form = $form->withRequest($request);
		$result = $form->getData();
	}
	else {
		$result = "No result yet.";
	}

	//Step 5: Render the form.
	return
		"<pre>".print_r($result, true)."</pre><br/>".
		$renderer->render($form);
}
