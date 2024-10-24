<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\ViewControl\Sortation;

/**
 * ---
 * description: >
 *   This can be used, when space is very scarce and the label can not be displayed
 *
 * expected output: >
 *   ILIAS shows a control with two arrows. Clicking the arrows will open a dropdown menu with three shy buttons
 *   "Default Ordering", "Most Recent Ordering" and "Oldest Ordering". Clicking the button will reload the website.
 *   The control is still the same as before.
 * ---
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

    //Note that no label is attached
    $s = $f->viewControl()->sortation($options)
        ->withTargetURL($DIC->http()->request()->getRequestTarget(), 'sortation');


    return $renderer->render($s);
}
