<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

function ten_items()
{
    //Init Factory and Renderer
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $c = $f->chart()->scaleBar(
        array(
            "0" => false,
            "1" => false,
            "2" => false,
            "3" => false,
            "4" => false,
            "5" => false,
            "6" => true,
            "7" => false,
            "8" => false,
            "9" => false
        )
    );

    //Render
    return $renderer->render($c);
}
