<?php

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

declare(strict_types=1);

namespace ILIAS\UI\examples\Player\Video;

/**
 * ---
 * description: >
 *  Example for rendering a mp4 video player.
 *
 * expected output: >
 *   ILIAS shows a rendered video player with a start screen. On the left side you will see a Start/Stop symbol,
 *   followed by a time bar and on the right side a symbol for subtitles (CC), volume control and for the the full screen.
 *   A big start symbol is shown in the middle of the start screen. While hovering over the subtitles symbol a list of all
 *   available languages appears. If a language gets selected you can find the text at the bottom of the full screen.
 *
 *   In addition following functions have to be tested:
 *   - The video starts playing if clicking the start/stop symbol in the middle of the image. The video stops after another click.
 *   - The sound fades or raises if the volumes gets changed by using the volume control.
 *   - Clicking the full screen icon maximizes the video player to the size of the desktop size. Clicking ESC will diminish the video player.
 * ---
 */
function video_mp4(): string
{
    global $DIC;
    $renderer = $DIC->ui()->renderer();
    $f = $DIC->ui()->factory();

    $video = $f->player()->video("https://files.ilias.de/ks/ILIAS-Video.mp4");
    $video = $video->withAdditionalSubtitleFile("en", "./assets/ui-examples/misc/subtitles_en.vtt");
    $video = $video->withAdditionalSubtitleFile("de", "./assets/ui-examples/misc/subtitles_de.vtt");

    return $renderer->render($video);
}
