<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Tree\Node\Simple;

function simple()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $icon = $f->symbol()->icon()->standard("crs", 'Example');

    $node = $f->tree()->node()->simple('label');
    $node2 = $f->tree()->node()->simple('label', $icon);

    return $renderer->render([$node, $node2]);
}
