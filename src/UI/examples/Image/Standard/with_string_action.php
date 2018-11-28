<?php
/**
 * Example for rendering an Image with a string as action
 */
function with_string_action() {
	//Loading factories
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	//Genarating and rendering the image and modal
	$image = $f->image()->standard(
		"src/UI/examples/Image/HeaderIconLarge.svg",
		"Thumbnail Example")->withAction("https://www.ilias.de");

	$html = $renderer->render($image);

	return $html;
}