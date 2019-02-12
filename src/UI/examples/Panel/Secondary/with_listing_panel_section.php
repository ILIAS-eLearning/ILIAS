<?php

function with_listing_panel_section() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$btn_back = $f->button()->standard("previous", "http://www.ilias.de");
	$btn_next = $f->button()->standard("next", "http://www.github.com");

	//month button or standard button can be used.
	$btn_month = $f->button()->month("02-2019")->withOnLoadCode(function($id) {
		return "$(\"#$id\").on('il.ui.button.month.changed', function(el, id, month) { alert(\"Clicked: \" + id + ' with ' + month);});";
	});

	$vc_section = $f->viewControl()->section($btn_back,$btn_month,$btn_next);

	$actions = $f->dropdown()->standard(array(
		$f->button()->shy("ILIAS", "https://www.ilias.de"),
		$f->button()->shy("GitHub", "https://www.github.com")
	));

	//LISTING PANEL
	$df = new \ILIAS\Data\Factory();

	$list_item1 = $f->item()->standard("Weekly Meeting")
		->withActions($actions)
		->withProperties(array(
			"Origin" => "Course Title 1",
			"Last Update" => "24.11.2011",
			"Location" => "Room 123, Main Street 44, 3012 Bern"))
		->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.")
		->withColor($df->color('#ff00ff'))
		->withLeadText("11:20 - 12:40");

	$list_item2 = $f->item()->standard("Tech VC")
		->withActions($actions)
		->withProperties(array(
			"Origin" => "Course Title 1",
			"Last Update" => "24.11.2011",
			"Location" => "Room 123, Main Street 44, 3012 Bern"))
		->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.")
		->withColor($df->color('#F9F9D0'))
		->withLeadText("13:00 - 14:00");

	$list_item3 = $f->item()->standard("Jour Fixe")
		->withActions($actions)
		->withProperties(array(
			"Origin" => "Course Title 1",
			"Last Update" => "24.11.2011",
			"Location" => "Room 123, Main Street 44, 3012 Bern"))
		->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.")
		->withColor($df->color('#000000'))
		->withLeadText("8:00 - 10:00");

	$listing_panel = $f->panel()->listing()->standard("Upcoming Events", array(
		$f->item()->group("Today", array(
			$list_item1,
			$list_item2
		)),
		$f->item()->group("Tomorrow", array(
			$list_item3
		))
	));

	$panel = $f->panel()->secondary(
		"Secondary Panel Title",
		$listing_panel)->withSection($vc_section)->withActions($actions);

	return $renderer->render($panel);
}
