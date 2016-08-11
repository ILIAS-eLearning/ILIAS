<?php

function base() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$sub1 = $f->panel()->sub("Sub Panel Title 1",$f->generic("Some Content"))
			->withCard($f->card("Card Heading")->withSections(array($f->generic("Card Content"))));
	$sub2 = $f->panel()->sub("Sub Panel Title 2",$f->generic("Some Content"));

	$block = $f->panel()->report("Report Title", array($sub1,$sub2));

	return $renderer->render($block);
}
