<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Card\Standard;

/**
 * ---
 * expected output: >
 *   ILIAS shows the rendered Component.
 * ---
 */
function with_sections()
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
        "./templates/default/images/logo/HeaderIcon.svg",
        "Thumbnail Example"
    );

    $card = $f->card()->standard(
        "Title",
        $image
    )->withSections(
        array(
            $content,
            $content,
            $content
        )
    );

    //Render
    return $renderer->render($card);
}
