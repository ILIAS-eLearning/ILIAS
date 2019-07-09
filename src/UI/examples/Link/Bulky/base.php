<?php
function base() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$ico = $f->symbol()->icon()
		->standard('someExample', 'Example')
		->withAbbreviation('E')
		->withSize('medium');

	$link = $f->link()->bulky($ico, 'Icon', '#');

	return $renderer->render($link);
}
