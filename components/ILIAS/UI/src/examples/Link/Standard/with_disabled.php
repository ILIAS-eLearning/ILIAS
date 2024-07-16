<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Link\Standard;

/**
 * ---
 * expected output: >
 *   ILIAS shows the rendered Component, but it is not operable.
 * ---
 */
function with_disabled()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $link = $f->link()->standard("Goto ILIAS", "http://www.ilias.de")
        ->withDisabled(true);
    return $renderer->render($link);
}
