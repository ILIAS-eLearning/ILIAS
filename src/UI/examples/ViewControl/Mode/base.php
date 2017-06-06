<?php
/**
 * Only serving as Example
 */
function base() {
	//Loading factories
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	//ViewControl element
	$view_control = $f->viewControl()->mode(array("Button 1" => "http://example.com", "Button 2" => "http://example.net", "Button 3" => "http://example.org"));
	$html = $renderer->render($view_control);

	return $html;
}