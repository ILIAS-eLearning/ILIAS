<?php
function base() {

	global $DIC;
	$uiFactory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$unorderedList = $uiFactory->listing()->unordered(
		["Point 1","Point 2","Point 3"]
	);

	$wrapperDropzone = $uiFactory->dropzone()->file()->wrapper($unorderedList);

	return $renderer->render($wrapperDropzone);
}