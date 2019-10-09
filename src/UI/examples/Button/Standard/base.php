<?php
function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    return $renderer->render($f->button()->standard("Goto ILIAS", "http://www.ilias.de"));
}
