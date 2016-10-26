<?php
/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

function user() {
	//Init Factory and Renderer
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$address = $f->listing()->descriptive(
		array(
			"Address" => "Hochschlustrasse 6",
			"" => "3006 Bern",
			"Contact" => "timon.amstutz@ilub.unibe.ch"
		)
	);

	$image = $f->image()->responsive(
		"./templates/default/images/HeaderIcon.svg", "Thumbnail Example");

	$card = $f->card(
		"Timon Amstutz",
		$image
	)->withSections(array($address,$f->button()->standard("Request Contact","")));

	$deck = $f->deck(array($card,$card,$card,$card,$card,$card,$card))
		->withCardsSize(ILIAS\UI\Component\Deck\Deck::SIZE_L);

	//Render
	return $renderer->render($deck);
}
