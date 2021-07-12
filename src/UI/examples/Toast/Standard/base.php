<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Toast\Standard;

/**
 * Base
 */
function base()
{
    global $DIC;
    $toast = $DIC->ui()->factory()->toast()->standard(
        'Example',
        $DIC->ui()->factory()->symbol()->icon()->standard('info', 'Test')
    );
    return $DIC->ui()->renderer()->render($toast);
}
