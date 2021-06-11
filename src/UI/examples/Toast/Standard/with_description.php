<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Toast\Standard;

function with_description()
{
    global $DIC;
    $toast = $DIC->ui()->factory()->toast()->standard(
        'Example',
        $DIC->ui()->factory()->symbol()->icon()->standard('info', 'Test')
    )->withDescription('This is an example description.');
    return $DIC->ui()->renderer()->render($toast);
}
