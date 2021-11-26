<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Item\Contribution;

function with_close()
{
    global $DIC;

    return $DIC->ui()->renderer()->render(
        $DIC->ui()->factory()->item()->contribution(
            'a little test contribution',
            'Contributor',
            new \DateTimeImmutable()
        )->withClose(
            $DIC->ui()->factory()->button()->close()
        )
    );
}
