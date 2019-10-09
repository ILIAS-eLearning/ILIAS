<?php
/**
 * Base Example for rendering an responsive Image
 */
function base()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the image
    $image = $f->image()->responsive(
        "src/UI/examples/Image/HeaderIconLarge.svg",
        "Thumbnail Example"
    );
    $html = $renderer->render($image);

    return $html;
}
