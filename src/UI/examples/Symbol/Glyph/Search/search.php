<?php
function search() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	return $renderer->render($f->symbol()->glyph()->search("#"));
}
