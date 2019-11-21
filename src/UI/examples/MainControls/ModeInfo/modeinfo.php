<?php
use ILIAS\Data\URI;

function modeinfo()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    return $renderer->render($f->mainControls()->modeInfo('a small step for a man', new URI('http://a_giant_leap_for_mankind_meaning')));
}

