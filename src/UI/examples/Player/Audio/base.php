<?php declare(strict_types=1);

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

namespace ILIAS\UI\examples\Player\Audio;

function base()
{
    global $DIC;
    $renderer = $DIC->ui()->renderer();
    $f = $DIC->ui()->factory();

    $audio = $f->player()->audio("https://files.ilias.de/ILIAS-Audio.mp3", "Erster Gesang: Pest im Lager. Zorn des Achilleus. Singe vom Ingrimm, Göttin, des Peleus-Sohnes Achilleus, vom Verfluchten, der zahllose Schmerzen schuf den Archaiern und viele kraftvolle Seelen der Helden vorwarf dem Hades...");

    return $renderer->render($audio);
}
