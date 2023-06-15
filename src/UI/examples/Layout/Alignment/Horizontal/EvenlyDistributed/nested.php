<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Layout\Alignment\Horizontal\EvenlyDistributed;

function nested()
{
    global $DIC;
    $ui_factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $blocks = [
        $ui_factory->legacy('<div style="background-color: lightblue; padding: 15px; height: 100%;">D</div>'),
        $ui_factory->legacy('<div style="background-color: lightgreen; padding: 15px; height: 100%;">E</div>'),
        $ui_factory->legacy('<div style="background-color: lightyellow; padding: 15px; height: 100%;">F</div>')
    ];

    $aligned = $ui_factory->layout()->alignment()->horizontal()->evenlyDistributed(
        $ui_factory->legacy('<div style="background-color: blue; padding: 15px; height: 100%; width: 150px;">A</div>'),
        $ui_factory->legacy('<div style="background-color: green; padding: 15px; height: 100%; width: 50px;">B</div>'),
        $ui_factory->legacy('<div style="background-color: yellow; padding: 15px; height: 100%; width: 200px;">C</div>')
    );

    return $renderer->render(
        $ui_factory->layout()->alignment()->horizontal()
            ->evenlyDistributed(
                $aligned,
                ...$blocks
            )
    );
}
