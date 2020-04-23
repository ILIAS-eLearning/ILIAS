<?php
//Async example show-casing how this control can be used, without reloading the page
function async()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Initializing the options, note that the label is taken care of by JS
    $options = [
        'default_option' => 'Default Ordering',
        'latest' => 'Most Recent Ordering',
        'oldest' => 'Oldest Ordering'
    ];

    //Note that the selected option needs to be displayed in the label
    $select_option = 'default_option';
    if ($_GET['sortation']) {
        $select_option = $_GET['sortation'];
    }

    //Generation of the UI Component
    $modal = $f->modal()->lightbox($f->modal()->lightboxTextPage('Note: This is just used to show case, how 
        this control can be used,to change an other components content.', "Sortation has changed: " . $options[$_GET['sortation']]));
    $s = $f->viewControl()->sortation($options)
            ->withTargetURL($DIC->http()->request()->getRequestTarget(), 'sortation')
            ->withLabel($options[$select_option])
            ->withOnSort($modal->getShowSignal());

    //Rendering
    return $renderer->render([$s,$modal]);
}
