<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

function xl_cards() {
	//Init Factory and Renderer
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$content = $f->listing()->descriptive(
		array(
			"Entry 1" => "Some text",
			"Entry 2" => "Some more text",
		)
	);

	$image = $f->image()->responsive(
		"./templates/default/images/HeaderIcon.svg", "Thumbnail Example");

	$card = $f->card(
		"Title",
		$image
	)->withSections(array(
		$content
	));

	$deck = $f->deck(array($card,$card,$card))
		->withCardsSize(ILIAS\UI\Component\Deck\Deck::SIZE_XL);

	//Render
	return $renderer->render($deck);
}
