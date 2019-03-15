<?php

function base() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$actions = $f->dropdown()->standard(array(
		$f->button()->shy("ILIAS", "https://www.ilias.de"),
		$f->button()->shy("GitHub", "https://www.github.com")
	));

	$legacy = $f->legacy("Legacy content");

	$panel = $f->panel()->secondary()->legacy(
		"Legacy panel title",
		$legacy)->withActions($actions);

	return $renderer->render($panel);
}