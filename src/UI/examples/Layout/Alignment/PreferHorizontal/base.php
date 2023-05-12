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
        $ui_factory->listing()->unordered(['1','2','3']),
        $ui_factory->listing()->unordered(['4','5', '6'])
    ];
    $sets[] = [
        $ui_factory->listing()->ordered(['a','b','c']),
        $ui_factory->image()->standard(
            "templates/default/images/HeaderIconResponsive.svg",
            "ilias"
        )
    ];


    $horizontal = $ui_factory->layout()->alignment()->preferHorizontal(...$sets);
    return $renderer->render($horizontal);
}
