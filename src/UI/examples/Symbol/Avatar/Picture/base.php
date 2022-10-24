<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Symbol\Avatar\Picture;

function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $picture_avatar = $f->symbol()->avatar()->picture('./templates/default/images/no_photo_xsmall.jpg', 'demo.user');

    return $renderer->render($picture_avatar);
}
