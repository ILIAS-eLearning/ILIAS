<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Layout\Alignment\ForceHorizontal;

function base()
{
    global $DIC;
    $ui_factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $sets = [];
    $sets[] = [
        $ui_factory->legacy('<div style="background-color: lightblue; padding: 15px; height: 100%;">Example Block</div>'),
        $ui_factory->legacy('<div style="background-color: lightgreen; padding: 15px; height: 100%;">Another Example Block</div>'),
        $ui_factory->legacy('<div style="background-color: lightyellow; padding: 15px; height: 100%;">And a third block is also part of this group</div>')
    ];
    $sets[] = [
        $ui_factory->panel()->standard(
            "A very informative panel",
            $ui_factory->legacy("<p>This panel could hold a little widget like it does on the dashboard.</p><p>However, it probably should not hold an entire view and other layout alignment components. That would just be too much.</p>")
        )->withActions($ui_factory->dropdown()->standard(array(
            $ui_factory->button()->shy("ILIAS", "https://www.ilias.de"),
            $ui_factory->button()->shy("GitHub", "https://www.github.com")
        )))
    ];
    $sets[] = [
        $ui_factory->image()->standard("templates/default/images/HeaderIconResponsive.svg", "ilias"),
        $ui_factory->image()->standard("templates/default/images/HeaderIconResponsive.svg", "ilias"),
        $ui_factory->image()->standard("templates/default/images/HeaderIconResponsive.svg", "ilias"),
        $ui_factory->image()->standard("templates/default/images/HeaderIconResponsive.svg", "ilias")
    ];

    $horizontal = $ui_factory->layout()->alignment()->forceHorizontal(...$sets);
    return $renderer->render($horizontal);
}
