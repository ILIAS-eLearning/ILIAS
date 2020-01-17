<?php
function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $image_avatar = $f->symbol()->avatar()->letter('demo.user');

    return $renderer->render($image_avatar);
}
