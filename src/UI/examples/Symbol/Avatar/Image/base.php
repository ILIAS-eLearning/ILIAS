<?php
function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $image_avatar = $f->symbol()->avatar()->image('./templates/default/images/no_photo_xsmall.jpg', 'demo.user');

    return $renderer->render($image_avatar);
}
