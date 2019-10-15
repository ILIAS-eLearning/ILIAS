<?php
function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    return $renderer->render($f->link()->standard("Goto ILIAS", "http://www.ilias.de"));
}
