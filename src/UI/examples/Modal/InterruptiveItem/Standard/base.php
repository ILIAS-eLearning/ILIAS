<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Modal\InterruptiveItem\Standard;

function base()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $message = 'Here you see some standard interruptive items:';
    $icon = $factory->image()->standard('./templates/default/images/icon_crs.svg', '');
    $modal = $factory->modal()->interruptive('My Title', $message, "#")
                     ->withAffectedItems(array(
                         $factory->modal()->interruptiveItem()->standard(
                             '10',
                             'Title of the Item',
                             $icon,
                             'Note, this item is currently only to be used in interruptive Modal.'
                         ),
                         $factory->modal()->interruptiveItem()->standard(
                             '20',
                             'Title of the other Item',
                             $icon,
                             'And another one.'
                         )
                     ));
    $button = $factory->button()->standard('Show some standard interruptive items', '')
                      ->withOnClick($modal->getShowSignal());


    return $renderer->render([$button, $modal]);
}
