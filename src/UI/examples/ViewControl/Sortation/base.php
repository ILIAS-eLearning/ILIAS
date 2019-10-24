<?php

function base()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $options = array(
        'internal_rating' => 'Best',
        'date_desc' => 'Most Recent',
        'date_asc' => 'Oldest',
    );

    $s = $f->viewControl()->sortation($options)
        ->withTargetURL($DIC->http()->request()->getRequestTarget(), 'sortation');

    $s2 = $s->withLabel($DIC->language()->txt('sortation_std_label'));


    $image = $f->image()->responsive("src/UI/examples/Image/mountains.jpg", "Image source: https://stocksnap.io, Creative Commons CC0 license");
    $page = $f->modal()->lightboxImagePage($image, 'Mountains');
    $modal = $f->modal()->lightbox($page);

    $s3 = $s->withResetSignals()
        ->withLabel('show Modal on select')
        ->withOnSort($modal->getShowSignal());


    return implode('<hr>', array(
        $renderer->render($s),
        $renderer->render($s2),
        $renderer->render([$s3, $modal]),
    ));
}
