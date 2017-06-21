<?php
/**
 * Only serving as Mockup
 * Todo: Replace with actual example as soon as implemented
 */
function mockup_2() {
	//Loading factories
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	//Genarating and rendering the mockup
	$image = $f->image()->responsive("src/UI/examples/ViewControl/Section/mockup2.png", "mockup2");
	$html = $renderer->render($image);
	return $html;
}
