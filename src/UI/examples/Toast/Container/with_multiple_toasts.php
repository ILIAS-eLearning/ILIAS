<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Toast\Container;

function with_multiple_toasts() : string
{
    global $DIC;
    $tc = $DIC->ui()->factory()->toast()->container()
        ->withAdditionalToast(
            $DIC->ui()->factory()->toast()->standard(
                'Example 1',
                $DIC->ui()->factory()->symbol()->icon()->standard('info', 'Test')
            )
        )->withAdditionalToast(
            $DIC->ui()->factory()->toast()->standard(
                'Example 2',
                $DIC->ui()->factory()->symbol()->icon()->standard('info', 'Test')
            )
        )->withAdditionalToast(
            $DIC->ui()->factory()->toast()->standard(
                'Example 3',
                $DIC->ui()->factory()->symbol()->icon()->standard('info', 'Test')
            )
        );

    return $DIC->ui()->renderer()->render($tc);
}
