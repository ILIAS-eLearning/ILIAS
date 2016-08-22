<?php

function with_card() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$block = $f->panel()->standard("Panel Title",
			$f->panel()->sub("Sub Panel Title",$f->generic("Some Content"))
			->withCard($f->card("Card Heading")->withSections(array($f->generic("Card Content"))))
	);

	return $renderer->render($block);
}
