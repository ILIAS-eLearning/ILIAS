<?php
function common() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();
	$captions = array(
		'opt1',
		'opt2',
		'opt3',
		'opt4',
		'opt5'
	);

	$ri = $f->input()->rating('topic', $captions);

	return $renderer->render($ri);
}