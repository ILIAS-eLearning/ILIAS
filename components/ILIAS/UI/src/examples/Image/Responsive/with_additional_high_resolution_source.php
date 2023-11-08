<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Image\Responsive;

/**
 * Example showing different card sizes which use an image with additional
 * high resolution sources. The image defaults to the smallest version of
 * the image (144px wide) and loads the next bigger version for different
 * breakpoints (min-widths). The effect is best seen on desktop devices.
 */
function with_additional_high_resolution_source(): string
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $image = $factory
        ->image()
        ->responsive('components/ILIAS/UI/src/examples/Image/mountains-144w.jpg', 'Mountains')
        ->withAdditionalHighResSource('components/ILIAS/UI/src/examples/Image/mountains-301w.jpg', 100)
        ->withAdditionalHighResSource('components/ILIAS/UI/src/examples/Image/mountains-602w.jpg', 300)
        ->withAdditionalHighResSource('components/ILIAS/UI/src/examples/Image/mountains.jpg', 500);

    $card = $factory->card()->standard('Mountains', $image);

    // render each card individually so every image has a different id.
    return
        '<div style="width: 100%; display: flex; justify-content: space-between">' .
        '<div style="width: 49%;">' . $renderer->render($card) . '</div>' .
        '<div style="width: 30%;">' . $renderer->render($card) . '</div>' .
        '<div style="width: 19%;">' . $renderer->render($card) . '</div>' .
        '</div>';
}
