<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\ViewControl\Sortation;

//Base example, show-casing how this control is used if firing leads to some
//Reload of the page
function base()
{
    //Load factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $refinery = $DIC->refinery();
    $request_wrapper = $DIC->http()->wrapper()->query();

    //Initialize options
    $options = [
        'default_option' => 'Default',
        'latest' => 'Most Recent',
        'oldest' => 'Oldest'
    ];

    //Note that the selected option needs to be displayed in the label
    $select_option = 'default_option';
    if ($request_wrapper->has('sortation') && $request_wrapper->retrieve('sortation', $refinery->kindlyTo()->string())) {
        $select_option = $request_wrapper->retrieve('sortation', $refinery->kindlyTo()->string());
    }

    //Generate the UI Component
    $s = $f->viewControl()->sortation($options, $select_option)
           ->withTargetURL($DIC->http()->request()->getRequestTarget(), 'sortation');

    //Rendering
    return $renderer->render($s);
}
