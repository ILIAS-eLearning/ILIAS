<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Video;

function video_mp4_poster()
{
    global $DIC;
    $renderer = $DIC->ui()->renderer();
    $f = $DIC->ui()->factory();

    $video = $f->video("./src/UI/examples/Video/hawaii-night.mp4");
    $video = $video->withPoster("./src/UI/examples/Video/poster.png");

    return $renderer->render($video);
}
