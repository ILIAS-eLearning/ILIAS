<?php
//This can be used, when space is very scarce and the label can not be displayed
function small()
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

    //Note that no label is attached
    $s = $f->viewControl()->sortation($options)
        ->withTargetURL($DIC->http()->request()->getRequestTarget(), 'sortation');
    
    return $renderer->render($s);
}
