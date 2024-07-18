<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Card\Standard;

function base()
{
    //Init Factory and Renderer
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $content = $f->listing()->descriptive(
        array(
            "Entry 1" => "Some text",
            "Entry 2" => "Some more text",
        )
    );

    $image = $f->image()->responsive(
        "./templates/default/images/HeaderIcon.svg",
        "Thumbnail Example"
    );

    $card = $f->card()->standard("Title", $image);

    //Render
    return $renderer->render($card);
}
