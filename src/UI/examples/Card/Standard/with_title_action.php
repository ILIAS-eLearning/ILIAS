<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Card\Standard;

function with_title_action()
{
    //Init Factory and Renderer
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $image = $f->image()->responsive(
        "./templates/default/images/HeaderIcon.svg",
        "Thumbnail Example"
    );

    $url = "http://www.ilias.de";

    $card = $f->card()->standard("Title", $image)->withTitleAction($url);

    //Render
    return $renderer->render($card);
}
