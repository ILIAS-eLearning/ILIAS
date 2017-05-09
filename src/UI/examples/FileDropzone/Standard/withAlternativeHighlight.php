<?php
function withAlternativeHighlight() {

	global $DIC;
	$uiFactory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();


	$standardDropzone = $uiFactory->fileDropzone()->standard()
		->withDarkendBackground(false);

	return $renderer->render($standardDropzone);
}