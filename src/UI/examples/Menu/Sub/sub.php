<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Menu\Sub;

function sub()
{
    $comment =
    '<p> The sub-menu is actually not meant to be rendered standalone.<br />'
    . 'However, it will generate a ul-tree with buttons for nodes.<p/>';

    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $sub = $f->menu()->sub('Schweiz (1)', [
        $f->menu()->sub('Bachflohkrebs (1.1)', []),
        $f->divider()->horizontal(),
        $f->menu()->sub('Wildkatze (1.2)', [
            $f->menu()->sub('gewöhnliche Wildkatze (1.2.1)', []),
            $f->menu()->sub('große Wildkatze (1.2.2)', []),
            $f->button()->standard('clickable', '', '#')
        ])
    ]);

    return $renderer->render([
        $f->legacy($comment),
        $sub
    ]);
}
