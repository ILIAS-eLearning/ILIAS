<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Divider\Horizontal;

function with_label()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    return $renderer->render($f->divider()->horizontal()->withLabel("Label"));
}
