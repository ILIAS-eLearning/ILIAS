<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Dropdown\Standard;

function with_buttons_and_links()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $items = array(
        $f->button()->shy("Github", "https://www.github.com"),
        $f->link()->standard("ILIAS", "https://www.ilias.de")->withOpenInNewViewport(true)
    );
    return $renderer->render($f->dropdown()->standard($items)->withLabel("Actions"));
}
