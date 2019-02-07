<?php

/**
 *TODO --> Simplify this example and create others.
 */
function with_actions() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	//SORTATION
	//TODO why sortations do not use a dropdown like actions?
	$sort_options = array(
		'internal_rating' => 'Best',
		'date_desc' => 'Most Recent',
		'date_asc' => 'Oldest',
	);
	$sortation = $f->viewControl()->sortation($sort_options);

	//PAGINATION
	$url = $DIC->http()->request()->getRequestTarget();
	$parameter_name = 'page';
	$current_page = (int)@$_GET[$parameter_name];

	$pagination = $f->viewControl()->pagination()
		->withTargetURL($url, $parameter_name)
		->withTotalEntries(98)
		->withPageSize(10)
		->withDropdownAt(5)
		->withCurrentPage($current_page);

	//ACTIONS
	$actions = $f->dropdown()->standard(array(
		$f->button()->shy("ILIAS", "https://www.ilias.de"),
		$f->button()->shy("GitHub", "https://www.github.com")
	));

	//PANEL GENERATION EMBEDDED
	$panel = $f->panel()->secondary(
		"Panel Title",
		$f->panel()->standard("Standard panel title",$f->legacy("Some Content")))->withSortation($sortation)->withPagination($pagination)->withActions($actions);

	return $renderer->render($panel);
}