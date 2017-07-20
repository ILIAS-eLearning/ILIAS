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

	return $renderer->render($s);
}
