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
 *   Example for rendering a mp4 video player with a poster as a start screen.
 *
 * expected output: >
 *   ILIAS shows a rendered video player with the ILIAS Logo as a start screen. On the left side you will see a Start/Stop
 *   symbol, followed by a time bar and on the right side a symbol for volume control and for the the full screen.
 *   A big start symbol is shown in the middle of the start screen.
 *
 *   In addition following functions have to be tested:
 *   - The video starts playing if clicking the start/stop symbol in the middle of the image. The video stops after another click.
 *   - The sound fades or raises if the volumes gets changed through the volume control.
 *   - Clicking the full screen icon maximizes the video player to the size of the desktop size. Clicking ESC will diminish the video player.
 * ---
 */
function video_mp4_poster(): string
{
    global $DIC;
    $renderer = $DIC->ui()->renderer();
    $f = $DIC->ui()->factory();

    $video = $f->player()->video("https://files.ilias.de/ILIAS-Video.mp4");
    $video = $video->withPoster("src/UI/examples/Image/HeaderIconLarge.svg");

    return $renderer->render($video);
}
