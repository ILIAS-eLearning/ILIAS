<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Contribution\Quote;

function with_identifier()
{
    global $DIC;

    return $DIC->ui()->renderer()->render(
        $DIC->ui()->factory()->contribution()->quote(
            'a little test contribution',
            'Contributor',
            new \DateTimeImmutable()
        )->withIdentifier('thisisaspecialidentifier')
    );
}
