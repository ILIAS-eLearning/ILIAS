<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Panel\Standard;

function base_text_block()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $panel = $f->panel()->standard(
        "Panel Title",
        $f->legacy("Some Content")
    );

    return $renderer->render($panel);
}
