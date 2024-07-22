<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Legacy;

function inside_panel()
{
    //Init Factory and Renderer
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Init Component
    $legacy = $f->legacy("Legacy Content");
    $panel = $f->panel()->standard("Panel Title", $legacy);

    //Render
    return $renderer->render($panel);
}
