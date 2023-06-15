<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Layout\Alignment\Vertical;

function nested()
{
    global $DIC;
    $ui_factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $icon = $ui_factory->image()->standard("templates/default/images/HeaderIconResponsive.svg", "ilias");

    $blocks = [
        $ui_factory->legacy('<div style="background-color: lightblue; padding: 15px; height: 100%;">Example Block</div>'),
        $icon,
        $ui_factory->legacy('<div style="background-color: lightgreen; padding: 15px; height: 100%;">Another Example Block</div>'),
        $icon,
        $ui_factory->legacy('<div style="background-color: lightyellow; padding: 15px; height: 100%; width: 100%">And a third block is also part of this group</div>')
    ];

    $dynamic = $ui_factory->layout()->alignment()->horizontal()->dynamicallyDistributed(...$blocks);
    $evenly = $ui_factory->layout()->alignment()->horizontal()->evenlyDistributed(
        $icon,
        $icon,
        $dynamic
    );


    $vertical = $ui_factory->layout()->alignment()->vertical(
        $ui_factory->legacy('<div style="background-color: red; padding: 15px; height: 100%;">The block above.</div>'),
        $evenly,
        $ui_factory->legacy('<div style="background-color: red; padding: 15px; height: 100%;">The block below.</div>')
    );


    return $renderer->render($vertical);
}
