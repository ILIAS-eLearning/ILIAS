<?php
function base()
{
    global $DIC;
    $f        = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $avatars = [];
    for ($x = 1; $x < 22; $x++) {
        $avatars[] = $f->symbol()->avatar()->letter(chr($x + 96));
    }

    return $renderer->render($avatars);
}
