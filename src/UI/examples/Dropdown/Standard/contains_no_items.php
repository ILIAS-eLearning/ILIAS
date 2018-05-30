<?php
function contains_no_items() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();
	return $renderer->render($f->dropdown()->standard([])->withLabel("Actions"));
}