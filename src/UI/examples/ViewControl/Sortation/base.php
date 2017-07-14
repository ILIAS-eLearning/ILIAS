<?php

function base() {
	//Loading factories
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

    $renderer = $DIC->ui()->renderer();
 
    $items = array(
		$f->button()->shy("Best", "https://www.ilias.de"),
		$f->button()->shy("Most Recent", "https://www.github.com")
	);
    return $renderer->render($f->dropdown()->standard($items)->withLabel("Sort By"));
}
