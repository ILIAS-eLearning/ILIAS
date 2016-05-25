<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

function Glyph_envelope() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$envelope = $f->glyph()->envelope();

	return "Envelope: ".$renderer->render($envelope)."</br>";
}
