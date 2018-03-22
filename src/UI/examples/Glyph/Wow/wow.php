<?php
function wow() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	return $renderer->render($f->glyph()->wow("#"));
}
