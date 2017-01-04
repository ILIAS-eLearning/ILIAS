<?php
function show_a_single_image() {
	global $DIC;
	$factory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();
	$button = $factory->button()->standard('Open Modal', '');
	$image = $factory->image()->responsive("src/UI/examples/Image/HeaderIconLarge.svg", "Thumbnail Example");
	$page = $factory->modal()->lightboxImagePage($image, 'My Title');
	$modal = $factory->modal()->lightbox($page);
	$connector = $factory->connector()->onClick($button, $modal->getShowAction());

	return implode($renderer->render([ $button, $modal ], $connector));
}