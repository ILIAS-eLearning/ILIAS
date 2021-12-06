<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Item\Contribution;

function with_long_content()
{
    global $DIC;

    return $DIC->ui()->renderer()->render(
        $DIC->ui()->factory()->item()->shy('Test shy Item')->withProperties(['Property' => 'Value'])
    );
}
