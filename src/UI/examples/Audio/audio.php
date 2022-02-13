<?php

namespace ILIAS\UI\examples\Audio;

function audio() : string
{
    global $DIC;
    $renderer = $DIC->ui()->renderer();
    $f = $DIC->ui()->factory();

    $audio = $f->audio("./src/UI/examples/Audio/ilias.mp3", "Erster Gesang: Pest im Lager. Zorn des Achilleus. Singe vom Ingrimm, Göttin, des Peleus-Sohnes Achilleus, vom Verfluchten, der zahllose Schmerzen schuf den Archaiern und viele kraftvolle Seelen der Helden vorwarf dem Hades...");

    return $renderer->render($audio);
}
