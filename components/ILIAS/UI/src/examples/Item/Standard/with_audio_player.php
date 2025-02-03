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

namespace ILIAS\UI\examples\Item\Standard;

/**
 * ---
 * description: >
 *   Example for rendering a standard item with an audio player.
 *
 * expected output: >
 *   ILIAS shows a box with the following informations: The ILIAS-Logo, "ILIAS Audio" as heading and a dummy text in small
 *   writings ("Lorem ipsum...") below. Beneath those you can see a player to play an audio file. Below that player a fine
 *   line and more informations about the lengths of the file are displayed. Additionally the box includes an action menu
 *   on the right top.
 * ---
 */
function with_audio_player(): string
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $actions = $f->dropdown()->standard(array(
        $f->button()->shy("ILIAS", "https://www.ilias.de"),
        $f->button()->shy("GitHub", "https://www.github.com")
    ));

    $audio = $f->player()->audio("https://files.ilias.de/ILIAS-Audio.mp3", "");

    $app_item = $f->item()->standard("ILIAS Audio")
        ->withActions($actions)
        ->withAudioPlayer($audio)
        ->withProperties(array(
            "Length" => "00:00:26"))
        ->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.")
        ->withLeadImage($f->image()->responsive(
            "assets/ui-examples/images/Image/HeaderIconLarge.svg",
            "Thumbnail Example"
        ));
    return $renderer->render($app_item);
}
