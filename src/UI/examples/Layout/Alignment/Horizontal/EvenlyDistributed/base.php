<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Layout\Alignment\Horizontal\EvenlyDistributed;

function base()
{
    global $DIC;
    $ui_factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $edl = $ui_factory->layout()->alignment()->horizontal()->evenlyDistributed(
        $ui_factory->legacy('<div style="background-color: lightblue; padding: 15px; height: 100%;">Example Block</div>'),
        $ui_factory->legacy('<div style="background-color: lightgreen; padding: 15px; height: 100%;">Another Example Block</div>'),
        $ui_factory->legacy('<div style="background-color: lightyellow; padding: 15px; height: 100%;">And a third block is also part of this group</div>')
    );

    return $renderer->render($edl);
}
