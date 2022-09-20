<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\ViewControl\Sortation;

//Base example, show-casing how this control is used if firing leads to some
//Reload of the page
function _Base()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $refinery = $DIC->refinery();
    $request_wrapper = $DIC->http()->wrapper()->query();

    //Initializing the options
    $options = [
        'default_option' => 'Default Ordering',
        'latest' => 'Most Recent Ordering',
        'oldest' => 'Oldest Ordering'
    ];

    //Note that the selected option needs to be displayed in the label
    $select_option = 'default_option';
    if ($request_wrapper->has('sortation') && $request_wrapper->retrieve('sortation', $refinery->kindlyTo()->string())) {
        $select_option = $request_wrapper->retrieve('sortation', $refinery->kindlyTo()->string());
    }

    //Generation of the UI Component
    $s = $f->viewControl()->sortation($options)
           ->withTargetURL($DIC->http()->request()->getRequestTarget(), 'sortation')
           ->withLabel($options[$select_option]);

    //Rendering
    return $renderer->render($s);
}
