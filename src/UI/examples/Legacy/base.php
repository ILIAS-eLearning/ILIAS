<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Legacy;

/**
 * ---
 * expected output: >
 *   ILIAS shows the rendered Component.
 * ---
 */
function base()
{
    //Init Factory and Renderer
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Init Component
    $legacy = $f->legacy("Legacy Content");

    //Render
    return $renderer->render($legacy);
}
