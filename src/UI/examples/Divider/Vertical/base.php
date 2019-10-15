<?php
function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    return $renderer->render(array($f->legacy("Some content"),
        $f->divider()->vertical(),
        $f->legacy("More content")));
}
