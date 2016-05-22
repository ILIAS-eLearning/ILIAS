<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

function Glyph_envelope() {
	global $DIC;
	$f = $DIC["UIFactory"]; // this should be $DIC->UI()->Factory();
	$renderer = $DIC["UIRenderer"]; // this should be $DIC->UI()->Renderer();

	$envelope = $f->glyph()->envelope();

	return "Envelope: ".$renderer->render($envelope, $renderer)."</br>";
}
