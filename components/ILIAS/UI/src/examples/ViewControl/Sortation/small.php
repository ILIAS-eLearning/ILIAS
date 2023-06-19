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
    $request_wrapper = $DIC->http()->wrapper()->query();
    $refinery = $DIC->refinery();

    $options = array(
        'default_option' => 'Default',
        'latest' => 'Most Recent',
        'oldest' => 'Oldest'
    );

    //Note that no label is attached, but "selected" is set:
    $select_option = null;
    if ($request_wrapper->has('sortation') && $request_wrapper->retrieve('sortation', $refinery->kindlyTo()->string())) {
        $select_option = $request_wrapper->retrieve('sortation', $refinery->kindlyTo()->string());
    }

    $s = $f->viewControl()->sortation($options)
        ->withTargetURL($DIC->http()->request()->getRequestTarget(), 'sortation')
        ->withSelected($select_option);

    return $renderer->render($s);
}
