<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Toast\Standard;

function with_additional_links()
{
    global $DIC;
    $toast = $DIC->ui()->factory()->toast()->standard(
        'Example',
        $DIC->ui()->factory()->symbol()->icon()->standard('info', 'Example')
    )
    ->withAdditionalLink($DIC->ui()->factory()->link()->standard('ILIAS', 'https://www.ilias.de'))
    ->withAdditionalLink($DIC->ui()->factory()->link()->standard('GitHub', 'https://www.github.com'))
    ;
    return $DIC->ui()->renderer()->render($toast);
}
