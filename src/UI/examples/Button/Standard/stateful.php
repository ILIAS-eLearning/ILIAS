<?php
function stateful()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $btn_engaged = $f->button()->standard("Goto ILIAS", "http://www.ilias.de")
        ->withEngagedState(true);

    return $renderer->render($btn_engaged);
}
