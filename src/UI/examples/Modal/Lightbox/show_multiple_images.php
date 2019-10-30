<?php
function show_multiple_images()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $image = $factory->image()->responsive('src/UI/examples/Image/mountains.jpg', 'Nice view on some mountains');
    $page = $factory->modal()->lightboxImagePage($image, 'Mountains', 'Image source: https://stocksnap.io, Creative Commons CC0 license');
    $image2 = $factory->image()->responsive('src/UI/examples/Image/sanfrancisco.jpg', 'The golden gate bridge');
    $page2 = $factory->modal()->lightboxImagePage($image2, 'San Francisco', 'Image source: https://stocksnap.io, Creative Commons CC0 license');
    $image3 = $factory->image()->responsive('src/UI/examples/Image/ski.jpg', 'Skiing');
    $page3 = $factory->modal()->lightboxImagePage($image3, 'Ski Fun', 'Image source: https://stocksnap.io, Creative Commons CC0 license');
    $modal = $factory->modal()->lightbox([$page, $page2, $page3]);
    $button = $factory->button()->standard('Show some fancy images', '')
        ->withOnClick($modal->getShowSignal());

    return $renderer->render([$button, $modal]);
}
