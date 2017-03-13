<?php
/**
 * Demo Example
 */
function b_Single() {
	//Loading factories
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	//Genarating and rendering the image

	$image1 = $f->image()->responsive(
		"src/UI/examples/Input/Selector/Repository/mockups/single1.png",
		"Existing 1");
	$image2 = $f->image()->responsive(
			"src/UI/examples/Input/Selector/Repository/mockups/single2.png",
			"Existing 1");
	$image3 = $f->image()->responsive(
			"src/UI/examples/Input/Selector/Repository/mockups/single3.png",
			"Existing 1");

	$html1 = $renderer->render($image1);
	$html2 = $renderer->render($image2);
	$html3 = $renderer->render($image3);

	return $html1.$html2.$html3;
}
