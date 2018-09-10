<?php

/* Copyright (c) 2018 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

function with_object_icon_and_progressmeter_standard
() {
	//Init Factory and Renderer
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$icon = $f->icon()->standard('crs', 'Course', 'medium');
	$progressmeter = $f->chart()->progressMeter()->standard(100,80);

	$content = $f->listing()->descriptive(
		array(
			"Entry 1" => "Some text",
			"Entry 2" => "Some more text",
		)
	);

	//testing images with other predominant colors.
	//"https://picsum.photos/900/900?image=893",
	//"https://picsum.photos/900/900?image=390",

	$image = $f->image()->responsive(
		"https://picsum.photos/900/900",
		"Thumbnail Example");

	$card = $f->card()->repositoryObject(
		"Title",
		$image
	)->withObjectIcon(
		$icon
	)->withProgress(
		$progressmeter
	)->withSections(
		array(
			$content,
			$content,
		)
	);

	//Render
	return $renderer->render($card);
}
