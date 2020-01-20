<?php
function base()
{
    global $DIC;
    $f        = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $avatars = [];
    for ($x = 0; $x < 26; $x++) {
        $chr       = chr($x + 97);
        $avatars[] = $f->symbol()->avatar()->letter($chr . $chr);
    }

    return $renderer->render($avatars);
}
