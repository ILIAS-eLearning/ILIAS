<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Toast\Container;

function with_toast() : string
{
    global $DIC;
    $tc = $DIC->ui()->factory()->toast()->container()->withAdditionalToast(
        $DIC->ui()->factory()->toast()->standard(
            'Example',
            $DIC->ui()->factory()->symbol()->icon()->standard('info', 'Test')
        )
    );

    return $DIC->ui()->renderer()->render($tc);
}
