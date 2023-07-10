<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\UI\examples\Player\Video;

function video_mp4(): string
{
    global $DIC;
    $renderer = $DIC->ui()->renderer();
    $f = $DIC->ui()->factory();

    $video = $f->player()->video("https://files.ilias.de/ILIAS-Video.mp4");
    $video = $video->withAdditionalSubtitleFile("en", "./src/UI/examples/Player/Video/subtitles_en.vtt");
    $video = $video->withAdditionalSubtitleFile("de", "./src/UI/examples/Player/Video/subtitles_de.vtt");

    return $renderer->render($video);
}
