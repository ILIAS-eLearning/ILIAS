<?php

function base() {
	//Loading factories
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	//split button or standard button can be used.
	$button = $f->button()->standard("Today", "");
	$view_control_section = $f->viewControl()->section("previous",$button,"next");
	$html = $renderer->render($view_control_section);
	return $html;
}
