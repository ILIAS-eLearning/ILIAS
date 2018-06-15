<?php
function success() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$buttons = [$f->button()->standard("Go to something", "#"), $f->button()->standard("Cancel", "#")];

	return $renderer->render($f->messageBox()->success("Success")->withButtons($buttons));
}