<?php

/* Copyright (c) 2018 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

function with_object_icon_and_certificate() {
	//Init Factory and Renderer
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$icon = $f->icon()->custom(ilUtil::getImagePath("icon_crs.svg"), 'Course', 'responsive');
	$certificate_icon = $f->icon()->custom(ilUtil::getImagePath("icon_cert.svg"), 'Certificate', 'responsive');


	$content = $f->listing()->descriptive(
		array(
			"Entry 1" => "Some text",
			"Entry 2" => "Some more text",
		)
	);

	$image = $f->image()->responsive(
		"./templates/default/images/HeaderIcon.svg",
		"Thumbnail Example");

	$card = $f->card()->repositoryObject(
		"Title",
		$image
	)->withObjectIcon(
		$icon
	)->withCertificateIcon(
		$certificate_icon
	)->withSections(
		array(
			$content,
			$content,
		)
	);

	//Render
	return $renderer->render($card);
}
