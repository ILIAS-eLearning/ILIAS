<?php
/**
 * Only serving as Mockup
 * Todo: Replace with actual example as soon as implemented
 */
function mockup_1() {
	//Loading factories
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	//Genarating and rendering the mockup
	$image = $f->image()->responsive("src/UI/examples/ViewControl/Section/mockup1.png", "mockup1");
	$html = $renderer->render($image);
	return $html;
}
