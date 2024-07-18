<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Player\Video;

function video_vimeo(): string
{
    global $DIC;
    $renderer = $DIC->ui()->renderer();
    $f = $DIC->ui()->factory();

    $video = $f->player()->video("https://vimeo.com/669475821?controls=0");

    return $renderer->render($video);
}
