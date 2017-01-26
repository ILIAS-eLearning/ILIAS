<?php
function show_a_single_image() {
	global $DIC;
	$factory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();
	$image = $factory->image()->responsive("src/UI/examples/Image/mountains.jpg", "Some mountains, does anyone know where? :)");
	$page = $factory->modal()->lightboxImagePage($image, 'Mountains');
	$modal = $factory->modal()->lightbox($page);
	$button = $factory->button()->standard('Show Image', '')
		->withOnClick($modal->getShowSignal());

	return implode($renderer->render([$button, $modal]));
}