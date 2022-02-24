<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Toast\Container;

function base() : string
{
    global $DIC;
    $tc = $DIC->ui()->factory()->toast()->container();
    return $DIC->ui()->renderer()->render($tc);
}
