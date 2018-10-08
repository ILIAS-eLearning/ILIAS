<?php

/* Copyright (c) 2018 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

function with_image_action() {
	//Init Factory and Renderer
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$icon = $f->icon()->custom(ilUtil::getImagePath("icon_crs.svg"), 'Course', 'responsive');

	$image = $f->image()->responsive(
		"./templates/default/images/HeaderIcon.svg",
		"Thumbnail Example");

	$url = "http://ilias.de";

	$content = $f->listing()->descriptive(
		array(
			"Entry 1" => "Some text",
			"Entry 2" => "Some more text",
		)
	);

	$card = $f->card()->repositoryObject(
		"Title",
		$image
	)->withObjectIcon(
		$icon
	)->withSections(
		array(
			$content,
			$content
		)
	)->withImageAction(
		$url
	);
	//Render
	return $renderer->render($card);
}
