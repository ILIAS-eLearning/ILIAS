<?php

function with_listing_panel_sortation() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$actions = $f->dropdown()->standard(array(
		$f->button()->shy("ILIAS", "https://www.ilias.de"),
		$f->button()->shy("GitHub", "https://www.github.com")
	));

	//SORTATION
	$sort_options = array(
		'internal_rating' => 'Best',
		'date_desc' => 'Most Recent',
		'date_asc' => 'Oldest',
	);
	$sortation = $f->viewControl()->sortation($sort_options);

	//LISTING PANEL
	$image = $f->image()->responsive(
		"src/UI/examples/Image/Avatar.png",
		"Thumbnail Example");

	$list_item1 = $f->item()->standard("Johnny Bravo")
		->withActions($actions)
		->withProperties(array(
			"Address" => "Main Street 44, 3012 Bern"))
		->withDescription("[user1]")
		->withLeadImage($image);

	$list_item2 = $f->item()->standard("Max Mustermann")
		->withActions($actions)
		->withProperties(array(
			"Address" => "Main Street 45, 3012 Bern"))
		->withDescription("[user2]")
		->withLeadImage($image);

	$list_item3 = $f->item()->standard("George Smith")
		->withActions($actions)
		->withProperties(array(
			"Address" => "Main Street 46, 3012 Bern"))
		->withDescription("[user3]")
		->withLeadImage($image);

	$listing_panel = $f->panel()->listing()->standard("", array(
		$f->item()->group("Your Contacts", array(
			$list_item1,
			$list_item2
		)),
		$f->item()->group("All Users", array(
			$list_item3
		))
	));

	$panel = $f->panel()->secondary(
		"Secondary Panel Title",
		$listing_panel)->withSortation($sortation)->withActions($actions);

	return $renderer->render($panel);

}
