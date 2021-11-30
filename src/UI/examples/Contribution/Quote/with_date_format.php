<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Contribution\Quote;

use ILIAS\Data\DateFormat\DateFormat;

function with_date_format()
{
    global $DIC;

    return $DIC->ui()->renderer()->render(
        $DIC->ui()->factory()->contribution()->quote(
            'a little test contribution',
            'Contributor',
            new \DateTimeImmutable()
        )->withDateFormat(
            new DateFormat([])
        )
    );
}
