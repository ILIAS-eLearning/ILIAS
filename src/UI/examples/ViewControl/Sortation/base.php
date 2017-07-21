<?php

function base() {
	//Loading factories
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$options = array(
		'internal_rating' => 'Best',
		'date_desc' => 'Most Recent',
	);

	$s = $f->viewControl()->sortation($options)
		->withLabel("ordering")
		->withParameterName("ord");

	//pre-selected
	$s2 = $f->viewControl()->sortation($options)
		->withLabel("Most Recent");

	return implode('<hr>', array(
		$renderer->render($s),
		$renderer->render($s2)
	));
}
