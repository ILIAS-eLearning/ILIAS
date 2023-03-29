<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Modal\Interruptive;

/**
 * An example showing how you can set a custom label for the
 * modals action- and cancel-button.
 */
function with_custom_labels()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $modal = $factory->modal()->interruptive(
        'Interrupting something',
        'Am I interrupting you?',
        '#'
    )->withActionButtonLabel(
        'Yeah you do!'
    )->withCancelButtonLabel(
        'Nah, not really'
    );

    $trigger = $factory->button()->standard('I will interrupt you', $modal->getShowSignal());

    return $renderer->render([$modal, $trigger]);
}
