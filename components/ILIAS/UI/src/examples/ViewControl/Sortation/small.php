<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\ViewControl\Sortation;

//This can be used, when space is very scarce and the label can not be displayed
function small()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $options = array(
        'default_option' => 'Default Ordering',
        'latest' => 'Most Recent Ordering',
        'oldest' => 'Oldest Ordering'
    );

    //Note that no label is attached
    $s = $f->viewControl()->sortation($options)
        ->withTargetURL($DIC->http()->request()->getRequestTarget(), 'sortation');


    return $renderer->render($s);
}
