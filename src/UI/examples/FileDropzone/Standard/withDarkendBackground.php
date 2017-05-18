<?php
function withDarkendBackground() {

	global $DIC;
	$uiFactory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();


	$standardDropzone = $uiFactory->dropzone()->standard()
		->withDarkendBackground(true);

	return $renderer->render($standardDropzone);
}