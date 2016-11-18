<?php
/**
 * Demo Example
 */
function a_Existing() {
	//Loading factories
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	//Genarating and rendering the image

	$image1 = $f->image()->responsive(
		"src/UI/examples/Input/Selector/Repository/mockups/existing1.png",
		"Existing 1");
	$image2 = $f->image()->responsive(
			"src/UI/examples/Input/Selector/Repository/mockups/existing2.png",
			"Existing 1");
	$image3 = $f->image()->responsive(
			"src/UI/examples/Input/Selector/Repository/mockups/existing3.png",
			"Existing 1");
	$image4 = $f->image()->responsive(
			"src/UI/examples/Input/Selector/Repository/mockups/existing4.png",
			"Existing 1");
	$html1 = "<h4>Administration</h4>".$renderer->render($image1);
	$html2 = "<h4>Personal Desktop</h4>".$renderer->render($image2);
	$html3 = "<h4>Test</h4>".$renderer->render($image3).$renderer->render($image4);

	return $html1.$html2.$html3;
}
