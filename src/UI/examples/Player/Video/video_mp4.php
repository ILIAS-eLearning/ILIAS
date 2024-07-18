<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Player\Video;

function video_mp4(): string
{
    global $DIC;
    $renderer = $DIC->ui()->renderer();
    $f = $DIC->ui()->factory();

    $video = $f->player()->video("https://files.ilias.de/ks/ILIAS-Video.mp4");
    $video = $video->withAdditionalSubtitleFile("en", "./src/UI/examples/Player/Video/subtitles_en.vtt");
    $video = $video->withAdditionalSubtitleFile("de", "./src/UI/examples/Player/Video/subtitles_de.vtt");

    return $renderer->render($video);
}
