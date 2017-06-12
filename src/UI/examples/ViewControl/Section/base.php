<?php

function base() {
	//Loading factories
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$data_f = new ILIAS\Data\Factory();
	$back = $data_f->link("", "http://www.ilias.de");
	$next = $data_f->link("", "http://www.github.com");

	//split button or standard button can be used.
	$button = $f->button()->standard("Today", "");
	$view_control_section = $f->viewControl()->section($back,$button,$next);
	$html = $renderer->render($view_control_section);
	return $html;
}
