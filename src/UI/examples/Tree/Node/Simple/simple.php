<?php
function simple()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $icon=$f->symbol()->icon()->standard("crs", 'Example');

    $node = $f->tree()->node()->simple('label');
    $node2 = $f->tree()->node()->simple('label', $icon);

    return $renderer->render([$node, $node2]);
}
