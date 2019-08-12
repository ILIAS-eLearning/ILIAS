<?php
function base() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();
	$target = new \ILIAS\Data\URI(
		$_SERVER['REQUEST_SCHEME'].
		'://'.
		$_SERVER['SERVER_NAME'].
		':'.
		$_SERVER['SERVER_PORT'].
		$_SERVER['SCRIPT_NAME'].
		'?'.
		$_SERVER['QUERY_STRING']
	);

	$ico = $f->symbol()->icon()
		->standard('someExample', 'Example')
		->withAbbreviation('E')
		->withSize('medium');

	$glyph = $f->symbol()->glyph()->briefcase();

	$link = $f->link()->bulky($ico, 'Icon', $target);
	$link2 = $f->link()->bulky($glyph, 'Glyph', $target);

	return $renderer->render([$link, $link2]);
}
