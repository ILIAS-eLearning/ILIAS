<?php
function with_async_data() {
	global $DIC;
	$ui = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	//Step 1: Define the text input field and attach some default value
	$multi_select_input = $ui->input()
	                         ->field()
	                         ->tagInput("Basic Multi-Select Input", "Just some basic input")
	                         ->withOptionsProviderURL('src/UI/examples/Input/Field/TagInput/cities.json');

	//Step 2: Define the form and attach the section.
	$form = $ui->input()->container()->form()->standard("#", [$multi_select_input]);

	//Step 4: Render the form with the text input field
	return $renderer->render($form);
}
