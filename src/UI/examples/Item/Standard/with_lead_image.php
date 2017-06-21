<?php
/**
 * With Lead Image
 */
function with_lead_image() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();
	$app_item = $f->item()->standard("ILIAS Course")
		->withActions(array(
			"IILAS" => "http://www.ilias.de",
			"Features" => "http://feature.ilias.de",
			"Bugs" => "http://www.ilias.de/mantis/"))
		->withProperties(array(
			"Origin" => "Course Title 1",
			"Last Update" => "24.11.2011",
			"Location" => "Room 123, Main Street 44, 3012 Bern"))
		->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.")
		->withLeadImage($f->image()->responsive(
			"src/UI/examples/Image/HeaderIconLarge.svg",
			"Thumbnail Example"));
	return $renderer->render($app_item);
}