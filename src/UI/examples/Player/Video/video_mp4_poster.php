<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Player\Video;

function video_mp4_poster(): string
{
    global $DIC;
    $renderer = $DIC->ui()->renderer();
    $f = $DIC->ui()->factory();

    $video = $f->player()->video("https://files.ilias.de/ILIAS-Video.mp4");
    $video = $video->withPoster("src/UI/examples/Image/HeaderIconLarge.svg");

    return $renderer->render($video);
}
