<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Toast\Standard;

/**
 * With a clickable linked title
 */
function with_title_action()
{
    global $DIC;
    $toast = $DIC->ui()->factory()->toast()->standard(
        'Example',
        $DIC->ui()->factory()->symbol()->icon()->standard('info', 'Test')
    )->withTitleAction('https://www.ilias.de');
    return $DIC->ui()->renderer()->render($toast);
}
