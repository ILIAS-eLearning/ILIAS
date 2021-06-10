<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Toast\Standard;

/**
 * With with additional links as clickable actions
 */
function with_additional_actions()
{
    global $DIC;
    $toast = $DIC->ui()->factory()->toast()->standard(
        'Example',
        $DIC->ui()->factory()->symbol()->icon()->standard('info', 'Example')
    )
    ->withAdditionalAction($DIC->ui()->factory()->link()->standard('ILIAS', 'https://www.ilias.de'))
    ->withAdditionalAction($DIC->ui()->factory()->link()->standard('GitHub', 'https://www.github.com'))
    ;
    return $DIC->ui()->renderer()->render($toast);
}
