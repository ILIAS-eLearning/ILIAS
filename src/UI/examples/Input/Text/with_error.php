<?php
function with_error() {
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

	$text = $f->input()
		->text("label", "a byline for the field.")
		->withError("There is an error in this input field. =(");

    return '<div class="form-horizontal">'.$renderer->render($text)."</div>";
}
