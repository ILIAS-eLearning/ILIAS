<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Item\Contribution;

use ILIAS\UI\Component\Symbol\Icon\Standard;

function with_lead_icon()
{
    global $DIC;

    return $DIC->ui()->renderer()->render(
        $DIC->ui()->factory()->item()->contribution(
            'a little test contribution',
            'Contributor',
            new \DateTimeImmutable()
        )->withLeadIcon(
            $DIC->ui()->factory()->symbol()->icon()->standard(Standard::GRP, 'conversation')
        )
    );
}
