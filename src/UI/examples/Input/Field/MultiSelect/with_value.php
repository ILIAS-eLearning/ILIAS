<?php
/**
 * Example show how to create and render a basic text input field with an error
 * attached to it. This example does not contain any data processing.
 */
function with_value() {
	//Step 0: Declare dependencies
	global $DIC;
	$ui = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	//Step 1: Define the text input field and attach some default value
	$multi_select_input = $ui->input()->field()->multiSelect([6 => 'root', 13 => 'anonymous', 42 => 'fschmid'], "Basic Multi-Select Input",
		"Just some basic input")->withValue([42]);

	//Step 2: Define the form and attach the section.
	$form = $ui->input()->container()->form()->standard("#", [$multi_select_input]);

	//Step 4: Render the form with the text input field
	return $renderer->render($form);
}
