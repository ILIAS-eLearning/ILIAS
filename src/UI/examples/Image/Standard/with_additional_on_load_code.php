<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Image\Standard;

/**
 * Example showing how JS-Code can be attached to images.
 * In this example, an alert is popping up a soon as the image
 * is clicked.
 */
function with_additional_on_load_code()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Generating and rendering the image and modal
    $image = $f->image()->standard(
        "src/UI/examples/Image/HeaderIconLarge.svg",
        "Thumbnail Example"
    )->withAction("#")
     ->withAdditionalOnLoadCode(function ($id) {
         return "$('#$id').click(function(e) { e.preventDefault(); alert('Image Onload Code')});";
     });

    $html = $renderer->render($image);

    return $html;
}
