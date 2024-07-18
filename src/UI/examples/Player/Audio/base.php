<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Player\Audio;

function base()
{
    global $DIC;
    $renderer = $DIC->ui()->renderer();
    $f = $DIC->ui()->factory();

    $audio = $f->player()->audio("https://files.ilias.de/ks/ILIAS-Audio.mp3", "Erster Gesang: Pest im Lager. Zorn des Achilleus. Singe vom Ingrimm, Göttin, des Peleus-Sohnes Achilleus, vom Verfluchten, der zahllose Schmerzen schuf den Archaiern und viele kraftvolle Seelen der Helden vorwarf dem Hades...");

    return $renderer->render($audio);
}
