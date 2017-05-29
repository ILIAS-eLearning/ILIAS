<?php
function base() {
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $items = array(
		$f->dropdown()->item("ILIAS", "https://www.ilias.de"),
		$f->dropdown()->item("GitHub", "https://www.github.com")
	);
    return $renderer->render($f->dropdown()->standard($items)->withLabel("Actions"));
}
