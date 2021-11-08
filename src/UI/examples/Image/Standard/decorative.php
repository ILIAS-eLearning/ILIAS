<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Image\Standard;

/**
 * Base Example for rendering an Image with only decorative purpose (see accessibility rules in images)
 */
function decorative()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Generating and rendering the image
    $image = $f->image()->standard(
        "src/UI/examples/Image/HeaderIconLarge.svg",
        ""
    );
    $html = $renderer->render($image);

    return $html;
}
