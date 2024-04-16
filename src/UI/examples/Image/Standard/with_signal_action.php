<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Image\Standard;

/**
 * ---
 * description: >
 *   Example for rendering an Image with a signal as action
 *
 * expected output: >
 *   ILIAS shows the rendered Component.
 * ---
 */
function with_signal_action()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Generating and rendering the image and modal
    $image_in_modal = $f->image()->standard(
        "src/UI/examples/Image/mountains.jpg",
        ""
    );
    $page = $f->modal()->lightboxImagePage($image_in_modal, "Nice view");
    $modal = $f->modal()->lightbox($page);

    $image = $f->image()->standard(
        "src/UI/examples/Image/HeaderIconLarge.svg",
        "Thumbnail Example"
    )->withAction($modal->getShowSignal());

    $html = $renderer->render([$image, $modal]);

    return $html;
}
