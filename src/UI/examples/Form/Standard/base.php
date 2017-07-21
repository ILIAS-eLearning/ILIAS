<?php
function base() {
    global $DIC;
    $ui = $DIC->ui()->factory();
	$trafo = new \ILIAS\Transformation\Factory();
	$data = new \ILIAS\Data\Factory();
	$validation = new \ILIAS\Validation\Factory($data);
    $renderer = $DIC->ui()->renderer();
	$request = $DIC->http()->request();

	$sum = $trafo->custom(function($vs) {
		list($l, $r) = $vs;
		$s = $l + $r;
		return "$l + $r = $s";
	});
	$valid_number = $validation->custom(function($v) {
		return in_array($v, ["one", "two", "three", "four", "five", "six", "seven", "eight", "nine", "ten"]);
	}, "This is not a number I know..."); 
	$from_name = $trafo->custom(function($v) {
		switch($v) {
			case "one" : return 1;
			case "two" : return 2;
			case "three" : return 3;
			case "four" : return 4;
			case "five" : return 5;
			case "six" : return 6;
			case "seven" : return 7;
			case "eight" : return 8;
			case "nine" : return 9;
			case "ten" : return 10;
		}
		throw new \LogicException("PANIC!");
	});

	$number_input = $ui->input()->text("number", "Put in the name of a number from one to ten.")
		->withConstraint($valid_number)
		->withTransformation($from_name);

	$form = $ui->form()->standard("", 
		[ $number_input->withLabel("Left")
		, $number_input->withLabel("Right")
		])
		->withTransformation($sum);

	if ($request->getMethod() == "POST") {
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
