<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Button\Primary;

function engaged()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $button = $f->button()->primary("Engaged Button", "#")
                                  ->withEngagedState(true);
    return $renderer->render($button);
}
