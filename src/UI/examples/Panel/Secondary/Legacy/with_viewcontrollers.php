<?php

function with_viewcontrollers()
{
	global $DIC;

	$factory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$actions = $factory->dropdown()->standard(array(
		$factory->button()->shy("ILIAS", "https://www.ilias.de"),
		$factory->button()->shy("GitHub", "https://www.github.com")
	));

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

	$panel = $factory->panel()->secondary()->legacy("panel title", $legacy)->withViewControls(array($sortation, $pagination))->withActions($actions);

	return $renderer->render($panel);
}