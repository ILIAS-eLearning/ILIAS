<?php

function base()
{
    //Init Factory and Renderer
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Generate List
    $descriptive = $f->listing()->descriptive(
        [
            "Title 1"=>"Description 1",
            "Title 2"=>"Description 2",
            "Title 3"=>"Description 3"]
    );

    //Render
    return $renderer->render($descriptive);
}
