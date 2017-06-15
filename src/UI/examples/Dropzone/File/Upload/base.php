<?php
function base() {

	global $DIC;
	$uiFactory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$content = $uiFactory->legacy('My Component');
	$uploadUrl = $_SERVER['REQUEST_URI'];

	$upload = $uiFactory->dropzone()->file()->upload($content, $uploadUrl);

	return $renderer->render($upload);
}