<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Modal\RoundTrip;

/**
 * An example showing how you can set a custom label for the
 * modals cancel-button.
 */
function with_custom_labels()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $modal = $factory->modal()->roundtrip(
        'Showing something off',
        [
            $factory->messageBox()->info('I am something.'),
        ]
    )->withCancelButtonLabel(
        'wow, that was something'
    );

    $trigger = $factory->button()->standard('I will show you something', $modal->getShowSignal());

    return $renderer->render([$modal, $trigger]);
}
