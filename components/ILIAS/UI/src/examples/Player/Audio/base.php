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

namespace ILIAS\UI\examples\Player\Audio;

/**
 * ---
 * description: >
 *   Base example for rendering a audio player.
 *
 * expected output: >
 *   ILIAS shows a rendered audio player colored black including a control button with the label "Transcript" underneath the audio player.
 *   On the left side you will see a Start/Stop symbol, followed by a time bar and further right a volume control.
 *   The control button will open a window with the title "Transcript" and the text from the audio file.
 *   Clicking the Start/Stop symbol will start the player and play the audio file. Clicking the symbol again will stop the audio file.
 * ---
 */
function base()
{
    global $DIC;
    $renderer = $DIC->ui()->renderer();
    $f = $DIC->ui()->factory();

    $audio = $f->player()->audio("https://files.ilias.de/ks/ILIAS-Audio.mp3", "Erster Gesang: Pest im Lager. Zorn des Achilleus. Singe vom Ingrimm, Göttin, des Peleus-Sohnes Achilleus, vom Verfluchten, der zahllose Schmerzen schuf den Archaiern und viele kraftvolle Seelen der Helden vorwarf dem Hades...");

    return $renderer->render($audio);
}
