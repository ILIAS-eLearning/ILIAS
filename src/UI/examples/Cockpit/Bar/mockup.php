<?php
/**
* Mockup of a CockpitBar
*/
function mockup() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$buf = array();

	$image = $f->image()->standard(
			"src/UI/examples/Cockpit/Bar/bar_closed.png",
			"");
	array_push($buf, $renderer->render($image));

	$image = $f->image()->standard(
			"src/UI/examples/Cockpit/Bar/bar_mobile.png",
			"");
	array_push($buf, $renderer->render($image));

	$image = $f->image()->standard(
			"src/UI/examples/Cockpit/Bar/bar_widescreen.png",
			"");
	array_push($buf, $renderer->render($image));


	return implode('<br><hr><br>', $buf);
}