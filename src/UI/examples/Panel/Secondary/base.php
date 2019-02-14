<?php

function base() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$actions = $f->dropdown()->standard(array(
		$f->button()->shy("ILIAS", "https://www.ilias.de"),
		$f->button()->shy("GitHub", "https://www.github.com")
	));

	$panel = $f->panel()->secondary(
		"Secondary Panel Title",
		$f->legacy("Legacy content here"))->withActions($actions);

	return $renderer->render($panel);
}
