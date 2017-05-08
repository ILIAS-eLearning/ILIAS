<?php
function base() {

	global $DIC;
	$uiFactory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();


	$standardDropzone = $uiFactory->fileDropzone()->standard();

	return $renderer->render($standardDropzone);
}