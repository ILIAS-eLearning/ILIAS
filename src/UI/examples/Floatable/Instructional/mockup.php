<?php
// This mockup shows, how an Instructional Flotable will look like.
function mockup() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$mockup = $f->image()->responsive("/src/UI/examples/Floatable/Instructional/mockup.png","Instructional Mockup");
	return $renderer->render($mockup);
}
