<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Button\Standard;

function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    return $renderer->render($f->button()->standard("Goto ILIAS", "http://www.ilias.de"));
}
