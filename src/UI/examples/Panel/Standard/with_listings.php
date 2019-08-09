<?php

function with_listings()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $content = array(
        $f->listing()->ordered(array("item 1","item 2","item 3")),
        $f->listing()->unordered(array("item 1","item 2","item 3"))
    );

    $panel = $f->panel()->standard("Panel Title", $content);

    return $renderer->render($panel);
}
