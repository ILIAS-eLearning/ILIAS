<?php

function with_viewcontrollers()
{
	global $DIC;
	$factory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$actions = $f->dropdown()->standard(array(
		$factory->button()->shy("ILIAS", "https://www.ilias.de"),
		$factory->button()->shy("GitHub", "https://www.github.com")
	));

	$list_item1 = $factory->item()->standard("Item Title")
		->withActions($actions)
		->withProperties(array(
			"Origin" => "Course Title 1",
			"Last Update" => "24.11.2011",
			"Location" => "Room 123, Main Street 44, 3012 Bern"))
		->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.");

	$list_item2 = $factory->item()->standard("Item 2 Title")
		->withActions($actions)
		->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.");

	$list_item3 = $factory->item()->standard("Item 3 Title")
		->withActions($actions)
		->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.");

	$items = array(
		$factory->item()->group("Listing Subtitle 1", array(
			$list_item1,
			$list_item2
		)),
		$f->item()->group("Listing Subtitle 2", array(
			$list_item3
		)));

	$legacy = $factory->legacy("Legacy content");

	$sort_options = array(
		'internal_rating' => 'Best',
		'date_desc' => 'Most Recent',
		'date_asc' => 'Oldest',
	);
	$sortation = $factory->viewControl()->sortation($sort_options);


	$url = $DIC->http()->request()->getRequestTarget();

	$parameter_name = 'page';
	$current_page = (int)@$_GET[$parameter_name];

	$pagination = $factory->viewControl()->pagination()
		->withTargetURL($url, $parameter_name)
		->withTotalEntries(98)
		->withPageSize(10)
		->withCurrentPage($current_page);

	$panel = $factory->panel()->secondary()->listing("Listing panel Title", $items)->withViewControls(array($pagination,$sortation))->withActions($actions);

	return $renderer->render($panel);

}
