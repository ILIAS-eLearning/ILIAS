<?php

function base()
{
    //Init Factory and Renderer
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Generate List
    $unordered = $f->listing()->unordered(
        ["Point 1","Point 2","Point 3"]
    );

    //Render
    return $renderer->render($unordered);
}
