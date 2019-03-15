<?php

function base() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$actions = $f->dropdown()->standard(array(
		$f->button()->shy("ILIAS", "https://www.ilias.de"),
		$f->button()->shy("GitHub", "https://www.github.com")
	));

	$sort_options = array(
		'internal_rating' => 'Best',
		'date_desc' => 'Most Recent',
		'date_asc' => 'Oldest',
	);
	$sortation = $f->viewControl()->sortation($sort_options);

	$legacy = $f->legacy("Legacy content");

	$panel = $f->panel()->secondary()->legacy(
		"Legacy panel title",
		$legacy)->withActions($actions)->withSortation($sortation);

	return $renderer->render($panel);
}