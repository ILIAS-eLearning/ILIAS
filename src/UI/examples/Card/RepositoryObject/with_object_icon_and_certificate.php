<?php

/* Copyright (c) 2018 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

function with_object_icon_and_certificate() {
	//Init Factory and Renderer
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$icon = $f->icon()->standard('crs', 'Course', 'medium');
	$certificate_icon = $f->icon()->standard('cert', 'Certificate', 'medium');


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
