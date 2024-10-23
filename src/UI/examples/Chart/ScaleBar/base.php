<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Chart\ScaleBar;

/**
 * ---
 * expected output: >
 *   ILIAS shows the rendered Component.
 * ---
 */
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
