<?php
/**
 * Example show how to create and render a basic filter.
 */
function base() {
	//Step 0: Declare dependencies
	global $DIC;
	$ui = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	//Step 1: Define some input fields to plug into the filter.
	$text_input1 = $ui->input()->field()->text("Text 1");
	$text_input2 = $ui->input()->field()->text("Text 2");
	$numeric_input1 = $ui->input()->field()->numeric("Number 1");
	$numeric_input2 = $ui->input()->field()->numeric("Number 2");

	//Step 3: Define the filter and attach the inputs. The filter is initially activated in this case.
	$filter = $ui->input()->container()->filter()->standard("#", "#", "#",
		"#","#", "#", [$text_input1, $text_input2, $numeric_input1, $numeric_input2],
		[true, true, true, true], true);

	//Step 4: Render the filter
	return $renderer->render($filter);
}
