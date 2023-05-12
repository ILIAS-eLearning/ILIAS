<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Layout\Alignment\PreferHorizontal;

function base()
{
    global $DIC;
    $ui_factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $sets = [];
    $sets[] = [
        $ui_factory->listing()->unordered(['1 1 1 1 1','2 2 2 2 2', '3 3 3 3 3']),
        $ui_factory->listing()->unordered(['4 4 4 4 4','5 5 5 5 5', '6 6 6 6 6']),
        $ui_factory->listing()->unordered(['7 7 7 7 7','8 8 8 8 8', '9 9 9 9 9'])
    ];
    $sets[] = [
        $ui_factory->listing()->ordered(['a a a a a','b b b b b','c c c c c'])
    ];
    $sets[] = [
        $ui_factory->image()->standard("templates/default/images/HeaderIconResponsive.svg", "ilias"),
        $ui_factory->image()->standard("templates/default/images/HeaderIconResponsive.svg", "ilias"),
        $ui_factory->image()->standard("templates/default/images/HeaderIconResponsive.svg", "ilias"),
        $ui_factory->image()->standard("templates/default/images/HeaderIconResponsive.svg", "ilias")
    ];
    $sets[] = [
        $ui_factory->listing()->unordered(['1 1 1 1 1','2 2 2 2 2', '3 3 3 3 3']),
        $ui_factory->listing()->unordered(['4 4 4 4 4','5 5 5 5 5', '6 6 6 6 6']),
        $ui_factory->listing()->unordered(['7 7 7 7 7','8 8 8 8 8', '9 9 9 9 9'])
    ];
    $sets[] = [
        $ui_factory->listing()->unordered(['1 1 1 1 1','2 2 2 2 2', '3 3 3 3 3']),
        $ui_factory->listing()->unordered(['4 4 4 4 4','5 5 5 5 5', '6 6 6 6 6']),
        $ui_factory->listing()->unordered(['7 7 7 7 7','8 8 8 8 8', '9 9 9 9 9'])
    ];

    $horizontal = $ui_factory->layout()->alignment()->preferHorizontal(...$sets);
    return $renderer->render($horizontal);
}
