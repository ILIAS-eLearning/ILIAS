<?php
function withMultipleInstances() {

	global $DIC;
	$uiFactory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$unorderedList = $uiFactory->listing()->unordered(
		["Point 1","Point 2","Point 3"]
	);

	$firstWrapperDropzone = $uiFactory->dropzone()->file()->wrapper($unorderedList);
	$secondWrapperDropzone = $uiFactory->dropzone()->file()->wrapper($unorderedList);

	return $renderer->render(array($firstWrapperDropzone, $secondWrapperDropzone));
}