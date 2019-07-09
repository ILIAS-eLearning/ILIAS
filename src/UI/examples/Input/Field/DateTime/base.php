<?php
/**
 * Base example showing how to plug date-inputs into a form
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
	$date = $ui->input()->field()->dateTime("Pick a date/time", "This is the byline text");
	$formatted = $date
		->withMinValue(new DateTimeImmutable())
		->withFormat($data->dateFormat()->germanShort());
	$time = $date->withTimeOnly(true);
	$both = $date->withUseTime(true);

	//setting a timezone will return a date with this timezone.
	$tz = 'Asia/Tokyo';
	$timezoned = $both->withTimezone($tz)->withByline('Result-value will have TZ '.$tz);

	//if you want a date converted to the timezone, do it on the date:
	$date_now = new DateTime('now');
	$date_zoned = new DateTime('now', new \DateTimeZone($tz));


	//here is the usage of Data/DateFormat
	$format = $timezoned->getFormat()->toString() .' H:i';
	$timezoned_preset1 = $timezoned->withValue($date_now->format($format))
		->withByline('This is local "now"');
	$timezoned_preset2 = $timezoned->withValue($date_zoned->format($format))
		->withByline('This is "now" in ' .$tz);

	//Step 2: define form and form actions
	$ctrl->setParameterByClass(
		'ilsystemstyledocumentationgui',
		'example_name',
		'date'
	);
	$form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');
	$form = $ui->input()->container()->form()->standard($form_action, [
		'date'=>$date,
		'formatted'=>$formatted,
		'time'=>$time,
		'both'=>$both,
		'timezoned'=>$timezoned,
		'timezoned_preset1'=>$timezoned_preset1,
		'timezoned_preset2'=>$timezoned_preset2
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
