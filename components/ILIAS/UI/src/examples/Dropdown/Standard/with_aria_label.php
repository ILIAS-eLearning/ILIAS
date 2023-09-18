<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Dropdown\Standard;

function with_aria_label()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $items = array(
        $f->button()->shy("GitHub", "https://www.github.com"),
        $f->button()->shy("Bugs", "https://mantis.ilias.de"),
    );
    return $renderer->render($f->dropdown()->standard($items)->withAriaLabel("MyLabel"));
}
