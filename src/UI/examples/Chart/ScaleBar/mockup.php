<?php
/**
 * Mockup
 */
function mockup() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();
	$image = $f->image()->responsive(
			"src/UI/examples/Chart/mockup.png",
			"Mockup");
	$html = $renderer->render($image);
	return $html;
}
