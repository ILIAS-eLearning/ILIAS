<?php
/**
 * Example show how to create and render a basic filter.
 */
function base() {
	//Step 0: Declare dependencies
	global $DIC;
	$ui = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	//Step 1: Define some input field to plug into the filter.
	$text_input = $ui->input()->field()->text("Basic Input", "Just some basic input");

	//Step 3: Define the filter and attach the input.
	$filter = $ui->input()->container()->filter()->standard([$text_input]);

	//Step 4: Render the filter
	return $renderer->render($filter);
}
