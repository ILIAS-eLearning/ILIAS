<?php
/**
 * Responsive Mockup
 */
function responsive() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();
	$image = $f->image()->standard(
			"src/UI/examples/Panel/Listing/Appointment/responsive.png",
			"");
	return $renderer->render($image);
}
