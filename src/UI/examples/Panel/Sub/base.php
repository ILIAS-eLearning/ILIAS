<?php

function base() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$block = $f->panel()->standard("Panel Title",
			$f->panel()->sub("Sub Panel Title",$f->generic("Some Content"))
	);

	return $renderer->render($block);
}
