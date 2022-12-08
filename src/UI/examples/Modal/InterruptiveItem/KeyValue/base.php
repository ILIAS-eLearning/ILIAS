<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Modal\InterruptiveItem\KeyValue;

function base()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $message = 'Here you see some key-value interruptive items:';
    $modal = $factory->modal()->interruptive('My Title', $message, "#")
                     ->withAffectedItems(array(
                         $factory->modal()->interruptiveItem()->keyValue(
                             '10',
                             'First Key',
                             'first value'
                         ),
                         $factory->modal()->interruptiveItem()->keyValue(
                             '20',
                             'Second Key',
                             'second value'
                         ),
                         $factory->modal()->interruptiveItem()->keyValue(
                             '30',
                             'Third Key',
                             'really really long value, much much longer than any value needs to be, that should be cut off at some point'
                         )
                     ));
    $button = $factory->button()->standard('Show some key-value interruptive Items', '')
                      ->withOnClick($modal->getShowSignal());


    return $renderer->render([$button, $modal]);
}
