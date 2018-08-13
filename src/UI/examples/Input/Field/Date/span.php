<?php
/**
 * Base example showing how to use groups to express a date/time-span.
 */
function span() {

	//Step 0: Declare dependencies
	global $DIC;

	$ui = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();
	$request = $DIC->http()->request();
	$ctrl = $DIC->ctrl();
	$data = new \ILIAS\Data\Factory();
	$validation = new \ILIAS\Validation\Factory($data);
	$trafo = new \ILIAS\Transformation\Factory();

	//Step 1: Implement transformation and constraints
	$duration = $trafo->custom(function($v) {
		list($from, $until) = $v;

		return ['start'=>$from, 'end'=>$until];
	});
	$from_before_until = $validation->custom(function($v) {
		return $v['start'] < $v['end'];
	}, "'from' must be before 'until'");


	//Step 2: define the inputs
	$date_from = $ui->input()->field()->date("From", "This is the byline text")
		->withFormat('DD.MM.YYYY HH:mm');
	$date_until = $date_from->withLabel('Until');

	//Step 3: Define the group, add the inputs to the group and attach the
	//transformation and constraint
	$group = $ui->input()->field()->group([$date_from, $date_until])
		->withAdditionalTransformation($duration)
		->withAdditionalConstraint($from_before_until);

	//Step 4: define form and form actions, attach the group
	$ctrl->setParameterByClass(
		'ilsystemstyledocumentationgui',
		'example_name',
		'duration'
	);
	$form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');
	$form = $ui->input()->container()->form()->standard($form_action, ['duration'=>$group]);

	//Step 5: implement some form data processing.
	if ($request->getMethod() == "POST"
		&& $request->getQueryParams()['example_name'] == "duration") {
		$form = $form->withRequest($request);
		 $group = $form->getInputs()["duration"];
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

	//Step 6: Render the form.
	return
		"<pre>".print_r($result, true)."</pre><br/>".
		$renderer->render($form);
}
