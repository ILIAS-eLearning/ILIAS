<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Toast\Standard;

function with_action() : string
{
    global $DIC;
    $tc = $DIC->ui()->factory()->toast()->container()->withAdditionalToast(
        $DIC->ui()->factory()->toast()->standard(
            'Example',
            $DIC->ui()->factory()->symbol()->icon()->standard('info', 'Test')
        )->withAction('https://www.ilias.de')
    );
    return $DIC->ui()->renderer()->render($tc);
}
