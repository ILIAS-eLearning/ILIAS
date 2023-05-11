<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Layout\Alignment\PreferHorizontal;

function base()
{
    global $DIC;
    $ui_factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $horizontal = $ui_factory->layout()->alignment()->preferHorizontal();
    $renderer = $DIC->ui()->renderer($horizontal);
}
