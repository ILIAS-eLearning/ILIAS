<?php

function base() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$panel = $f->panel()->secondary(
		"Panel Title",
		$f->legacy("Some Content")
	);

	return $renderer->render($panel);
}
