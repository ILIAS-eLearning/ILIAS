<?php
/**
* Mockup of a CockpitBar
*/
function mockup() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();
	$image = $f->image()->standard(
			"src/UI/examples/Cockpit/Bar/bar_closed.png",
			"");
	return $renderer->render($image);
}