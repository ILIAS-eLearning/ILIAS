<?php
function show_multiple_texts()
{
    global $DIC;
    $factory  = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $page1    = $factory->modal()->lightboxTextPage('Some text content you have to agree on!', 'User Agreement');
    $page2    = $factory->modal()->lightboxTextPage(
        'Another text content you have to agree on!',
        'Data Privacy Statement'
    );
    $modal    = $factory->modal()->lightbox([$page1, $page2]);
    $button   = $factory->button()->standard('Show Texts', '')
        ->withOnClick($modal->getShowSignal());

    return $renderer->render([$button, $modal]);
}
