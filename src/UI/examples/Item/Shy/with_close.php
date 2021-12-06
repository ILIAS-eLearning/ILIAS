<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Item\Shy;

function with_close()
{
    global $DIC;

    return $DIC->ui()->renderer()->render(
        $DIC->ui()->factory()->item()->shy('Test shy Item')->withClose(
            $DIC->ui()->factory()->button()->close()
        )
    );
}
