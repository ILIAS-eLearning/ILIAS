<?php
function show_multiple_images() {
	global $DIC;
	$factory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();
	$image = $factory->image()->responsive('src/UI/examples/Image/mountains.jpg', 'Nice view on some mountains');
	$page = $factory->modal()->lightboxImagePage($image, 'Mountains');
	$image2 = $factory->image()->responsive('src/UI/examples/Image/sanfrancisco.jpg', 'The golden gate bridge');
	$page2 = $factory->modal()->lightboxImagePage($image2, 'San Francisco');
	$image3 = $factory->image()->responsive('src/UI/examples/Image/ski.jpg', 'Skiing');
	$page3 = $factory->modal()->lightboxImagePage($image3, 'Ski Fun');
	$modal = $factory->modal()->lightbox([$page, $page2, $page3]);
	$button = $factory->button()->standard('Show some fancy images', '')
		->withOnClick($modal->getShowSignal());

	return implode($renderer->render([$button, $modal]));
}