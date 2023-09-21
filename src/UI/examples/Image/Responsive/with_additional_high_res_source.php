<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Image\Responsive;

/**
 * Example showing the addition of high res sources
 */
function with_additional_high_res_source()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Generating and rendering the image
    $image = $f->image()->responsive(
        'src/UI/examples/Image/mountains-144w.jpg',
        'High Res Source Example'
    )->withAdditionalHighResSource('src/UI/examples/Image/mountains-301w.jpg', 300)
        ->withAdditionalHighResSource('src/UI/examples/Image/mountains-602w.jpg', 600);
    $html = '<div style="width: 30%;">' . $renderer->render($image) . '</div>';

    return $html;
}
