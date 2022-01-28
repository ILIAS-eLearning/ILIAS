<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Divider\Vertical;

function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    return $renderer->render(array($f->legacy("Some content"),
        $f->divider()->vertical(),
        $f->legacy("More content")));
}
