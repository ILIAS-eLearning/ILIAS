<?php
function base() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$ico = $f->symbol()->icon()
		->standard('someExample', 'Example')
		->withAbbreviation('E')
		->withSize('medium');

	$glyph = $f->symbol()->glyph()->briefcase();

	$link = $f->link()->bulky($ico, 'Icon', '#');
	$link2 = $f->link()->bulky($glyph, 'Glyph', '#');

	return $renderer->render([$link, $link2]);
}
