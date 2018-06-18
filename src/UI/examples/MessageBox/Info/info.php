<?php
function info() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$buttons = [$f->button()->standard("Go to something", "#"), $f->button()->standard("Cancel", "#")];

	$links = [
		$f->link()->standard("Link One", "#"),
		$f->link()->standard("Link Two", "#")
	];

	return $renderer->render($f->messageBox()->info("Info")
		->withButtons($buttons)
		->withLinks($links));
}