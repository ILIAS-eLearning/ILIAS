<?php

function base_text_block() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$panel = $f->panel()->standard(
			"Panel Title",
			$f->generic("Some Content")
	);

	return $renderer->render($panel);
}
