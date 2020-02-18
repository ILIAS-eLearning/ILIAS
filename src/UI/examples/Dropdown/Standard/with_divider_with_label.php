<?php
function with_divider_with_label()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $items = array(
        $f->button()->shy("GitHub", "https://www.github.com"),
        $f->divider()->horizontal()->withLabel("ILIAS"),
        $f->button()->shy("Docu", "https://www.ilias.de"),
        $f->button()->shy("Features", "https://feature.ilias.de"),
        $f->button()->shy("Bugs", "https://mantis.ilias.de"),
    );
    return $renderer->render($f->dropdown()->standard($items)->withLabel("Actions"));
}
