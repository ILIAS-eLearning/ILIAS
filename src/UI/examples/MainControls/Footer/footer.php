<?php
function footer()
{
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$text = 'Additional info:';
	$links = [];
	$links[] = $f->link()->standard("Goto ILIAS", "http://www.ilias.de");
	$links[] = $f->link()->standard("Goto ILIAS", "http://www.ilias.de");

	$footer = $f->mainControls()->footer($links, $text);

	return $renderer->render($footer);
}