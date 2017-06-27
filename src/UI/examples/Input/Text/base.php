<?php
function base() {
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

	$text = $f->input()->text("label", "a byline for the field.");

    return $renderer->render($text);
}
