<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Toast\Standard;

/**
 * With a action on vanishing. (Does not provide any visual representation by itself)
 */
function with_action() : string
{
    global $DIC;
    $toast = $DIC->ui()->factory()->toast()->standard(
        'Example',
        $DIC->ui()->factory()->symbol()->icon()->standard('info', 'Test')
    )->withAction('https://www.ilias.de');
    return $DIC->ui()->renderer()->render($toast);
}
