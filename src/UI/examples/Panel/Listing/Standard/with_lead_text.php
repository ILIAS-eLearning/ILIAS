<?php
/**
 * With lead text and marker
 */
function with_lead_text() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();
	$list_item1 = $f->item()->standard("Weekly Meeting")
		->withActions(array(
			"IILAS" => "http://www.ilias.de",
			"Features" => "http://feature.ilias.de",
			"Bugs" => "http://www.ilias.de/mantis/"))
		->withProperties(array(
			"Origin" => "Course Title 1",
			"Last Update" => "24.11.2011",
			"Location" => "Room 123, Main Street 44, 3012 Bern"))
		->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.")
		->withMarkerId(1)
		->withLeadText("11:20 - 12:40");

	$list_item2 = $f->item()->standard("Tech VC")
		->withActions(array(
			"ILIAS" => "http://www.ilias.de"))
		->withProperties(array(
			"Origin" => "Course Title 1",
			"Last Update" => "24.11.2011",
			"Location" => "Room 123, Main Street 44, 3012 Bern"))
		->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.")
		->withMarkerId(2)
		->withLeadText("13:00 - 14:00");

	$list_item3 = $f->item()->standard("Jour Fixe")
		->withActions(array(
			"ILIAS" => "http://www.ilias.de"))
		->withProperties(array(
			"Origin" => "Course Title 1",
			"Last Update" => "24.11.2011",
			"Location" => "Room 123, Main Street 44, 3012 Bern"))
		->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.")
		->withMarkerId(3)
		->withLeadText("8:00 - 10:00");

	$std_list = $f->panel()->listing()->standard("Upcoming Events", array(
		$f->panel()->listing()->divider()->withLabel("Today"),
		$list_item1,
		$list_item2,
		$f->panel()->listing()->divider()->withLabel("Tomorrow"),
		$list_item3
	));


	return $renderer->render($std_list);
}