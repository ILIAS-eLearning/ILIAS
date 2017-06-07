<?php
/**
 * Base
 */
function base() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();
	include_once("./Services/Calendar/classes/class.ilDateTime.php");
	$app_item = $f->item()->appointment("Item Title",
		new ilDateTime("2017-12-01 12:00:00"),
		new ilDateTime("2017-12-01 14:00:00"),
		"FF77FF"
	)->withActions(array(
		"IILAS" => "http://www.ilias.de",
		"Features" => "http://feature.ilias.de",
		"Bugs" => "http://www.ilias.de/mantis/"
		)
	)->withProperties(array(
		"Origin" => "Course Title 1",
		"Last Update" => "24.11.2011",
		"Location" => "Room 123, Main Street 44, 3012 Bern"
		)
	)->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.");
	return $renderer->render($app_item);
}
