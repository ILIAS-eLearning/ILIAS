<?php
/**
 * Base
 */
function base() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();
	$actions = $f->dropdown()->standard(array(
		$f->button()->shy("ILIAS", "https://www.ilias.de"),
		$f->button()->shy("GitHub", "https://www.github.com")
	));
	$app_item = $f->item()->standard("Item Title")
		->withActions($actions)
		->withProperties(array(
			"Origin" => "Course Title 1",
			"Last Update" => "24.11.2011",
			"Location" => "Room 123, Main Street 44, 3012 Bern"))
		->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.");
	return $renderer->render($app_item);
}