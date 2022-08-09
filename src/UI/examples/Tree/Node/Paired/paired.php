<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Tree\Node\Paired;

function paired()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $icon = $f->symbol()->icon()->standard("crs", 'Example');

    $node = $f->tree()->node()->paired('label', 'value');
    $node2 = $f->tree()->node()->paired('label', 'value', $icon);

    return $renderer->render([$node, $node2]);
}
