<?php
function base() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	return $renderer->render($f->button()->split()->standard(array(
		"ILIAS" => "https://www.ilias.de",
		"Feature Wiki" => "http://feature.ilias.de",
		"Blog" => "http://blog.ilias.de",
		"Bugs" => "http://mantis.ilias.de"
	)));
}