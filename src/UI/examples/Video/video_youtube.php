<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Video;

function video_youtube()
{
    global $DIC;
    $renderer = $DIC->ui()->renderer();
    $f = $DIC->ui()->factory();

    $video = $f->video("https://www.youtube.com/watch?v=YSN2osYbshQ");

    return $renderer->render($video);
}
