<?php
function with_average() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$buffer = array();
	$averages = array(0, 3, 5, 2.5, 3.74);

	foreach ($averages as $av) {
		$ri = $f->input()->rating('with average ' .(string)$av)
			->withAverage(floatval($av));
		$buffer[] = $renderer->render($ri);
	 }

	return implode('<br>', $buffer);
}
