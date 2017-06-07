<?php
/**
 * Base
 */
function base() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();
	$list_item1 = $f->item()->standard("Item Title")
		->withActions(array(
			"IILAS" => "http://www.ilias.de",
			"Features" => "http://feature.ilias.de",
			"Bugs" => "http://www.ilias.de/mantis/"))
		->withProperties(array(
			"Origin" => "Course Title 1",
			"Last Update" => "24.11.2011",
			"Location" => "Room 123, Main Street 44, 3012 Bern"))
		->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.")
		->withMarkerId(2);

	$std_list = $f->panel()->listing()->standard("List Title", array(
		$list_item1
	));


	return $renderer->render($std_list);
}