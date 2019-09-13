<?php
function eyeclosed() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$glyph = $f->symbol()->glyph()->eyeclosed("#");

	//Showcase the various states of this Glyph
	$list = $f->listing()->descriptive([
		"Active"=>$glyph,
		"Inactive"=>$glyph->withUnavailableAction(),
		"Highlighted"=>$glyph->withHighlight()
	]);

	return $renderer->render($list);
}
