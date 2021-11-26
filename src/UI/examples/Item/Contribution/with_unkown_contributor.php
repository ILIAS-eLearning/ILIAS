<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Item\Contribution;

function with_unkown_contributor()
{
    global $DIC;

    return $DIC->ui()->renderer()->render(
        $DIC->ui()->factory()->item()->contribution(
            'a little test contribution',
        )
    );
}
