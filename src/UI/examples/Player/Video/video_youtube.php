<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Player\Video;

/**
 * ---
 * expected output: >
 *   ILIAS shows the rendered Component.
 * ---
 */
function video_youtube(): string
{
    global $DIC;
    $renderer = $DIC->ui()->renderer();
    $f = $DIC->ui()->factory();

    $video = $f->player()->video("https://www.youtube.com/watch?v=YSN2osYbshQ");

    return $renderer->render($video);
}
