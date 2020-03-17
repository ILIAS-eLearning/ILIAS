<?php
function show_different_page_types()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $image = $factory->image()->responsive('src/UI/examples/Image/mountains.jpg', 'Nice view on some mountains');
    $page = $factory->modal()->lightboxImagePage(
        $image,
        'Mountains',
        'Image source: https://stocksnap.io, Creative Commons CC0 license'
    );

    $page2 = $factory->modal()->lightboxTextPage('Some text content you have to agree on!', 'User Agreement');

    $image2 = $factory->image()->responsive('src/UI/examples/Image/sanfrancisco.jpg', 'The golden gate bridge');
    $page3 = $factory->modal()->lightboxImagePage(
        $image2,
        'San Francisco',
        'Image source: https://stocksnap.io, Creative Commons CC0 license'
    );

    $page4 = $factory->modal()->lightboxTextPage(
        'Another text content you have to agree on!',
        'Data Privacy Statement'
    );

    $image3 = $factory->image()->responsive('src/UI/examples/Image/ski.jpg', 'Skiing');
    $page5 = $factory->modal()->lightboxImagePage(
        $image3,
        'Ski Fun',
        'Image source: https://stocksnap.io, Creative Commons CC0 license'
    );

    $modal = $factory->modal()->lightbox([$page, $page2, $page3, $page4, $page5]);
    $button = $factory->button()->standard('Show some fancy images and texts', '')
        ->withOnClick($modal->getShowSignal());

    return $renderer->render([$button, $modal]);
}
