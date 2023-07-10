<?php

declare(strict_types=1);

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\examples\Chart\ScaleBar;

function base()
{
    //Init Factory and Renderer
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $c = $f->chart()->scaleBar(
        array(
            "None" => false,
            "Low" => false,
            "Medium" => true,
            "High" => false
        )
    );

    //Render
    return $renderer->render($c);
}
