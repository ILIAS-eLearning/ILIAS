<?php
function confirmation() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$buttons = [$f->button()->standard("Confirm", "#"), $f->button()->standard("Cancel", "#")];

	return $renderer->render($f->messageBox()->confirmation("Confirmation")->withButtons($buttons));
}