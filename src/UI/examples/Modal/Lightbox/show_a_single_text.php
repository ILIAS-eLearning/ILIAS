<?php
function show_a_single_text()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $page = $factory->modal()->lightboxTextPage('Some text content you have to agree on!', 'User Agreement');
    $modal = $factory->modal()->lightbox($page);
    $button = $factory->button()->standard('Show Text', '')
        ->withOnClick($modal->getShowSignal());

    return $renderer->render([$button, $modal]);
}
