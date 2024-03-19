<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\ViewControl\Sortation;

/**
 * This can be used, when space is very scarce and the label cannot be displayed
 */
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

    //Hide the label
    $s = $f->viewControl()->sortation($options, 'oldest')
        ->withTargetURL($DIC->http()->request()->getRequestTarget(), 'sortation')
        ->withHiddenLabel(true);


    return $renderer->render($s);
}
