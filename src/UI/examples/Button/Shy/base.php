<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Button\Shy;

function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    return $renderer->render($f->button()->shy("ILIAS", "http://www.ilias.de"));
}
