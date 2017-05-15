<?php
function withDefaultMessage() {

	global $DIC;
	$uiFactory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$standardDropzone = $uiFactory->fileDropzone()->standard()
		->withMessage("Drop files here to upload.");

	return $renderer->render($standardDropzone);
}