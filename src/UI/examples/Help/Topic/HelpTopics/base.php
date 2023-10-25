<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Help\Topic\HelpTopics;

function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $primary = $f->button()->primary("Goto ILIAS", "http://www.ilias.de")
        ->withHelpTopics(
            ...$f->helpTopics("ilias", "learning management system")
        );
    return $renderer->render($primary);
}
