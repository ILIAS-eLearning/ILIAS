<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Video;

function video_mp4()
{
    global $DIC;
    $renderer = $DIC->ui()->renderer();
    $f = $DIC->ui()->factory();

    $video = $f->video("./src/UI/examples/Video/hawaii-night.mp4");
    $video = $video->withAdditionalSubtitleFile("en", "./src/UI/examples/Video/subtitles_en.vtt");
    $video = $video->withAdditionalSubtitleFile("de", "./src/UI/examples/Video/subtitles_de.vtt");

    return $renderer->render($video);
}
