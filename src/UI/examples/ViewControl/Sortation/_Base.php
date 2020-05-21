<?php
//Base example, show-casing how this control is used if firing leads to some
//Reload of the page
function _Base()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Initializing the options
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
    $s = $f->viewControl()->sortation($options)
           ->withTargetURL($DIC->http()->request()->getRequestTarget(), 'sortation')
           ->withLabel($options[$select_option]);

    //Rendering
    return $renderer->render($s);
}
