<?php
function simple() {

	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$node = $f->tree()->node()->simple('label');

	return $renderer->render($node);
}