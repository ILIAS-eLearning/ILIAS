<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Image\Standard;

/**
 * Base Example for rendering an Image
 */
function base()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Generating and rendering the image
    $image = $f->image()->standard(
        "src/UI/examples/Image/HeaderIconLarge.svg",
        "Thumbnail Example"
    );
    $html = $renderer->render($image);

    return $html;
}
