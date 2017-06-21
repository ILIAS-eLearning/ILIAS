<?php
/**
* Mockup of CockpitSlates
*/
function mockup() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$buf = array();

	$image = $f->image()->standard(
			"src/UI/examples/Cockpit/Slate/slate_tree.png",
			"");
	array_push($buf, $renderer->render($image));

	$image = $f->image()->standard(
			"src/UI/examples/Cockpit/Slate/slate_admin.png",
			"");
	array_push($buf, $renderer->render($image));

	$image = $f->image()->standard(
			"src/UI/examples/Cockpit/Slate/slate_search.png",
			"");
	array_push($buf, $renderer->render($image));


	return implode('<br><hr><br>', $buf);
}