<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Modal\InterruptiveItem;

function base()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $message = 'Here you see an interruptive Item:';
    $icon = $factory->image()->standard('./templates/default/images/icon_crs.svg', '');
    $modal = $factory->modal()->interruptive('My Title', $message, "#")
                     ->withAffectedItems(array(
                         $factory->modal()->interruptiveItem(
                             '10',
                             'Title of the Item',
                             $icon,
                             'Note, this item is currently only to be used in interruptive Modal.'
                         ),
                         $factory->modal()->interruptiveItem(
                             '20',
                             'Title of the other Item',
                             $icon,
                             'And another one.'
                         )

                     ));
    $button = $factory->button()->standard('Show an interruptive Item', '')
                      ->withOnClick($modal->getShowSignal());


    return $renderer->render([$button, $modal]);
}
