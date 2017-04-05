<?php
function common() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$captions = array(
		'I hate it',
		'it\'s OK',
		'I\'m completely undecided',
		'good',
		'I love it!'
	);

	$ri = $f->input()->rating('topic')
		->withCaptions($captions);

	return $renderer->render($ri);
}