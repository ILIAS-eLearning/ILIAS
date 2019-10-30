<?php
/**
 * Base Example for rendering an Image with only decorative purpose (see accessibility rules in images)
 */
function decorative()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the image
    $image = $f->image()->standard(
        "src/UI/examples/Image/HeaderIconLarge.svg",
        ""
    );
    $html = $renderer->render($image);

    return $html;
}
